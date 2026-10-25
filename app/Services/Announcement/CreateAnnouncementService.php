<?php

namespace App\Services\Announcement;

use App\Exceptions\AppException;
use App\Jobs\NotificationJobs\SendAdminAnnouncementScheduleReminderNotiJob;
use App\Jobs\NotificationJobs\SendAdminScheduledAnnouncementNotiJob;
use App\Models\Announcement;
use App\Models\Announcement\AnnouncementRecipient;
use App\Models\AnnouncementTag;
use App\Models\AnnouncementAuthor;
use App\Services\Audience\AudienceResolverService;
use App\Services\Audience\ResolvedAudienceService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class CreateAnnouncementService
{
    public function __construct(private AudienceResolverService $audienceResolver) {}

    public function createAnnouncement(object $currentSchool, array $authenticatedUser, array $data)
    {
        $isDraft = ($data['status'] ?? null) === 'draft';
        $tags = $this->getTags($data);

        $audience = null;
        if (!$isDraft) {
            $audience = $this->audienceResolver->resolve($currentSchool, $data);

            if ($audience->hasMissing()) {
                throw new AppException(
                    "Some Recipients Not Found",
                    404,
                    "Some Recipients Not Found",
                    "One or more selected recipients were not found for this school branch: "
                        . collect($audience->missing())->map(fn($ids, $type) => count($ids) . " " . $type)->implode(', ')
                        . ".",
                    null
                );
            }

            if ($audience->isEmpty()) {
                throw new AppException(
                    "No Recipients Found",
                    400,
                    "No Recipients Found",
                    "No valid recipients were found for the selected audience configuration. Please check your selections.",
                    null
                );
            }
        }

        return $this->createAnnouncementContent($currentSchool, $authenticatedUser, $data, $tags, $audience);
    }

    protected function getTags(array $data): Collection
    {
        if (empty($data['tag_ids'])) {
            return collect();
        }

        $tagIds = collect($data['tag_ids'])->pluck('tag_id')->filter()->unique()->toArray();
        $tags = AnnouncementTag::whereIn("id", $tagIds)->get();

        if ($tags->count() < count($tagIds)) {
            throw new AppException(
                "Some Tags Not Found",
                404,
                "Some Tags Not Found",
                "One or more selected tags could not be found. Please check that the tags exist and have not been deleted.",
                null
            );
        }

        return $tags;
    }

    public function createAnnouncementContent(object $currentSchool, array $authenticatedUser, array $data, Collection $tags, ?ResolvedAudienceService $audience = null)
    {
        return DB::transaction(function () use ($currentSchool, $authenticatedUser, $data, $tags, $audience) {
            $isDraft = ($data['status'] ?? null) === 'draft';
            $hasPublishedAt = !empty($data['published_at']);
            $intendedScheduled = ($data['status'] ?? null) === 'scheduled' || ($hasPublishedAt && Carbon::parse($data['published_at'])->isFuture());

            if ($intendedScheduled && !$hasPublishedAt) {
                throw new AppException(
                    "Published Time Required for Scheduled Announcement",
                    400,
                    "Published Time Required",
                    "You are trying to schedule an announcement, but no published time was provided. Please specify a future date and time.",
                    null
                );
            }

            $publishedAt = $isDraft ? null : ($hasPublishedAt ? Carbon::parse($data['published_at']) : Carbon::now());
            $isScheduled = !$isDraft && $hasPublishedAt && $publishedAt->isFuture();

            if (!$isDraft && $hasPublishedAt && $publishedAt->isPast()) {
                throw new AppException(
                    "Published Time Cannot Be in the Past",
                    400,
                    "Invalid Published Time",
                    "The provided published time is in the past. Please provide a future date or leave it blank for immediate publication.",
                    null
                );
            }

            if ($isScheduled && $publishedAt->diffInDays(Carbon::now()) > 30) {
                throw new AppException(
                    "Scheduled Time Too Far in the Future",
                    400,
                    "Scheduled Time Limit Exceeded",
                    "The scheduled publication time is more than 30 days in the future. Please choose a closer date to avoid long queue delays.",
                    null
                );
            }

            $status = $isDraft ? 'draft' : ($isScheduled ? 'scheduled' : 'active');

            if (($data['status'] ?? null) === 'scheduled' && $status !== 'scheduled') {
                throw new AppException(
                    "Invalid Scheduled Configuration",
                    400,
                    "Scheduled Announcement Misconfigured",
                    "You specified a scheduled status, but the provided published time is not in the future or is invalid. Please correct the published time.",
                    null
                );
            }

            $expiresAt = $isDraft ? null : $publishedAt->copy()->addDays(7);

            $announcement = Announcement::create([
                'title' => $data['title'],
                'content' => $data['content'],
                'status' => $status,
                'published_at' => $publishedAt,
                'expires_at' => $expiresAt,
                'category_id' => $data['category_id'] ?? null,
                'label_id' => $data['label_id'] ?? null,
                'notification_sent_at' => null,
                'audience' => json_encode([
                    'admin_audience' => $data['admin_audience'] ?? [],
                    'student_audience' => $data['student_audience'] ?? [],
                    'teacher_audience' => $data['teacher_audience'] ?? [],
                ]),
                'tags' => json_encode($tags->toArray()),
                'school_branch_id' => $currentSchool->id,
            ]);

            if ($audience && !$audience->isEmpty()) {
                $this->seedAudienceTable($currentSchool, $announcement->id, $audience);
            }

            AnnouncementAuthor::create([
                'school_branch_id' => $currentSchool->id,
                'authorable_id' => $authenticatedUser['userId'],
                'authorable_type' => $authenticatedUser['userType'],
                'announcement_id' => $announcement->id,
            ]);

            if ($status === 'scheduled') {
                SendAdminScheduledAnnouncementNotiJob::dispatch($announcement->id, $authenticatedUser, $currentSchool->id);

                if ($publishedAt->greaterThan(now()->addMinutes(10))) {
                    SendAdminAnnouncementScheduleReminderNotiJob::dispatch($announcement->id, $authenticatedUser, $currentSchool->id)
                        ->delay($publishedAt->copy()->subMinutes(5));
                }
            }

            return $announcement;
        });
    }

    private function seedAudienceTable(object $currentSchool, string $announcementId, ResolvedAudienceService $audience): void
    {
        $now = Carbon::now();

        $audience->recipients()->chunk(1000)->each(function ($chunk) use ($currentSchool, $announcementId, $now) {
            AnnouncementRecipient::insert(
                $chunk->map(fn($recipient) => [
                    "id" => Str::uuid()->toString(),
                    "announcement_id" => $announcementId,
                    "school_branch_id" => $currentSchool->id,
                    "recipient_id" => $recipient['id'],
                    "recipient_type" => $recipient['type'],
                    "seen_at" => null,
                    "created_at" => $now,
                    "updated_at" => $now,
                ])->values()->all()
            );
        });
    }
}
