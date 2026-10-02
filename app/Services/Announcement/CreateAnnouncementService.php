<?php

namespace App\Services\Announcement;

use App\Exceptions\AppException;
use App\Jobs\NotificationJobs\SendAdminAnnouncementScheduleReminderNotiJob;
use App\Jobs\NotificationJobs\SendAdminScheduledAnnouncementNotiJob;
use App\Models\Announcement;
use App\Models\Announcement\AnnouncementRecipient;
use App\Models\AnnouncementTag;
use App\Models\AnnouncementAuthor;
use App\Models\Schooladmin;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class CreateAnnouncementService
{
    public function createAnnouncement(object $currentSchool, array $authenticatedUser, array $data)
    {
        $recipients = $this->collectRecipients($currentSchool, $data);
        $tags = $this->getTags($data);

        if ($recipients->isEmpty()) {
            throw new AppException(
                "No Recipients Found",
                400,
                "No Recipients Found",
                "No valid recipients were found for the selected audience configuration. Please check your selections.",
                null
            );
        }

        return $this->createAnnouncementContent($currentSchool, $authenticatedUser, $data, $tags, $recipients);
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

    protected function collectRecipients(object $currentSchool, array $data): Collection
    {
        $recipients = collect();

        if (!empty($data['admin_audience'])) {
            $admins = $this->resolveAdminAudience($currentSchool, $data['admin_audience']);
            $recipients = $recipients->merge($admins);
        }

        if (!empty($data['teacher_audience'])) {
            $teachers = $this->resolveTeacherAudience($currentSchool, $data['teacher_audience']);
            $recipients = $recipients->merge($teachers);
        }

        if (!empty($data['student_audience'])) {
            $students = $this->resolveStudentAudience($currentSchool, $data['student_audience']);
            $recipients = $recipients->merge($students);
        }

        return $recipients->unique(function ($actor) {
            return get_class($actor) . '_' . $actor->id;
        });
    }

    private function resolveAdminAudience(object $currentSchool, array $audienceBlocks): Collection
    {
        $individualIds = collect($audienceBlocks)->pluck('individual_ids')->flatten()->filter()->unique()->toArray();

        if (empty($individualIds)) {
            return collect();
        }

        $admins = Schooladmin::where("school_branch_id", $currentSchool->id)
            ->whereIn("id", $individualIds)
            ->get();

        if ($admins->count() < count($individualIds)) {
            throw new AppException(
                "Some School Admins Not Found",
                404,
                "Some School Admins Not Found",
                "One or more selected school admins were not found for this school branch.",
                null
            );
        }

        return $admins;
    }

    private function resolveTeacherAudience(object $currentSchool, array $audienceBlocks): Collection
    {
        $blocks = collect($audienceBlocks);

        $departmentIds = $blocks->pluck('department_ids')->flatten()->filter()->unique()->toArray();
        $specialtyIds  = $blocks->pluck('specialty_ids')->flatten()->filter()->unique()->toArray();
        $levelIds      = $blocks->pluck('level_ids')->flatten()->filter()->unique()->toArray();
        $individualIds = $blocks->pluck('individual_ids')->flatten()->filter()->unique()->toArray();

        $query = Teacher::where("school_branch_id", $currentSchool->id)
            ->where(function ($q) use ($departmentIds, $specialtyIds, $levelIds, $individualIds) {
                if (!empty($individualIds)) {
                    $q->orWhereIn('id', $individualIds);
                }

                if (!empty($specialtyIds)) {
                    $q->orWhereHas('specialty', fn($sq) => $sq->whereIn('id', $specialtyIds));
                }

                if (!empty($departmentIds)) {
                    $q->orWhereHas('specialty.department', fn($dq) => $dq->whereIn('id', $departmentIds));
                }

                if (!empty($levelIds)) {
                    $q->orWhereHas('specialty.level', fn($lq) => $lq->whereIn('id', $levelIds));
                }
            });

        return $query->get();
    }

    private function resolveStudentAudience(object $currentSchool, array $audienceBlocks): Collection
    {
        $blocks = collect($audienceBlocks);

        $departmentIds = $blocks->pluck('department_ids')->flatten()->filter()->unique()->toArray();
        $specialtyIds  = $blocks->pluck('specialty_ids')->flatten()->filter()->unique()->toArray();
        $levelIds      = $blocks->pluck('level_ids')->flatten()->filter()->unique()->toArray();
        $individualIds = $blocks->pluck('individual_ids')->flatten()->filter()->unique()->toArray();

        $query = Student::where("school_branch_id", $currentSchool->id)
            ->where(function ($q) use ($departmentIds, $specialtyIds, $levelIds, $individualIds) {
                if (!empty($individualIds)) {
                    $q->orWhereIn('id', $individualIds);
                }

                if (!empty($specialtyIds)) {
                    $q->orWhereIn('specialty_id', $specialtyIds);
                }

                if (!empty($departmentIds)) {
                    $q->orWhereHas('specialty', fn($sq) => $sq->whereIn('department_id', $departmentIds));
                }

                if (!empty($levelIds)) {
                    $q->orWhereHas('specialty', fn($sq) => $sq->whereIn('level_id', $levelIds));
                }
            });

        return $query->get();
    }

    public function createAnnouncementContent(object $currentSchool, array $authenticatedUser, array $data, Collection $tags, Collection $recipients)
    {
        try {
            return DB::transaction(function () use ($currentSchool, $authenticatedUser, $data, $tags, $recipients) {
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
                    'category_id' => $data['category_id'],
                    'label_id' => $data['label_id'],
                    'notification_sent_at' => null,
                    'audience' => json_encode([
                        'admin_audience' => $data['admin_audience'] ?? [],
                        'student_audience' => $data['student_audience'] ?? [],
                        'teacher_audience' => $data['teacher_audience'] ?? [],
                    ]),
                    'tags' => json_encode($tags->toArray()),
                    'school_branch_id' => $currentSchool->id,
                ]);

                $this->seedAudienceTable($currentSchool, $announcement->id, $recipients);

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
        } catch (Throwable $e) {
            throw $e;
        }
    }

    private function seedAudienceTable(object $currentSchool, string $announcementId, Collection $recipients): void
    {
        $now = Carbon::now();

        $records = $recipients->map(function ($actor) use ($currentSchool, $announcementId, $now) {
            $morphType = method_exists($actor, 'getMorphClass') ? $actor->getMorphClass() : get_class($actor);

            return [
                "id" => Str::uuid()->toString(),
                "announcement_id" => $announcementId,
                "school_branch_id" => $currentSchool->id,
                "recipient_id" => $actor->id,
                "recipient_type" => $morphType,
                "seen_at" => null,
                "created_at" => $now,
                "updated_at" => $now,
            ];
        })->toArray();

        if (!empty($records)) {
            AnnouncementRecipient::insert($records);
        }
    }
}
