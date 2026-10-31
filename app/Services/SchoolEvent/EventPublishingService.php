<?php

namespace App\Services\SchoolEvent;

use App\Events\Actions\AdminActionEvent;
use App\Exceptions\AppException;
use App\Jobs\DataCleanupJobs\UpdateSchoolEventStatusJob;
use App\Jobs\DataCreationJob\PublishSchoolEventJob;
use App\Jobs\NotificationJobs\SendAdminEventCreatedNotificationJob;
use App\Jobs\NotificationJobs\SendAdminEventScheduleReminderNotiJob;
use App\Jobs\NotificationJobs\SendAdminScheduledSchoolEventNotiJob;
use App\Models\EventAudience;
use App\Models\EventTag;
use App\Models\SchoolEvent;
use App\Services\Audience\AudienceResolverService;
use App\Services\Audience\ResolvedAudienceService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class EventPublishingService
{
    public function __construct(private AudienceResolverService $audienceResolver) {}

    public function isSchoolWide(array $data): bool
    {
        return filter_var($data['school_wide'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    public function hasAudienceInput(array $data): bool
    {
        return array_key_exists('school_wide', $data)
            || array_key_exists('admin_audience', $data)
            || array_key_exists('student_audience', $data)
            || array_key_exists('teacher_audience', $data);
    }

    public function audiencePayload(array $data): array
    {
        if ($this->isSchoolWide($data)) {
            return ['school_wide' => true];
        }

        return [
            'admin_audience' => $data['admin_audience'] ?? [],
            'student_audience' => $data['student_audience'] ?? [],
            'teacher_audience' => $data['teacher_audience'] ?? [],
        ];
    }

    public function resolveAudience(object $currentSchool, array $audience): ResolvedAudienceService
    {
        $resolved = $this->audienceResolver->resolve($currentSchool, $audience);

        if ($resolved->hasMissing()) {
            throw new AppException(
                "Some Invitees Not Found",
                404,
                "Some Invitees Not Found",
                "One or more selected invitees were not found for this school branch: "
                    . collect($resolved->missing())->map(fn($ids, $type) => count($ids) . " " . $type)->implode(', ')
                    . ".",
                null
            );
        }

        if ($resolved->isEmpty()) {
            throw new AppException(
                "No Invitees Found",
                400,
                "No Invitees Found",
                "No valid invitees were found for the selected audience configuration. Please check your selections.",
                null
            );
        }

        return $resolved;
    }

    public function getTags(array $data): Collection
    {
        if (empty($data['tag_ids'])) {
            return collect();
        }

        $tagIds = collect($data['tag_ids'])->pluck('tag_id')->filter()->unique()->values()->toArray();
        $tags = EventTag::whereIn("id", $tagIds)->get();

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

    public function resolveTiming(?string $requestedStatus, $publishedAtInput, $startDate): array
    {
        if ($requestedStatus === 'draft') {
            return ['draft', null];
        }

        $hasPublishedAt = !empty($publishedAtInput);
        $intendedScheduled = $requestedStatus === 'scheduled' || ($hasPublishedAt && Carbon::parse($publishedAtInput)->isFuture());

        if ($intendedScheduled && !$hasPublishedAt) {
            throw new AppException(
                "Published Time Required for Scheduled School Event",
                400,
                "Published Time Required",
                "You are trying to schedule a School Event, but no published time was provided. Please specify a future date and time.",
                null
            );
        }

        $publishedAt = $hasPublishedAt ? Carbon::parse($publishedAtInput) : Carbon::now();

        if ($hasPublishedAt && $publishedAt->isPast()) {
            throw new AppException(
                "Published Time Cannot Be in the Past",
                400,
                "Invalid Published Time",
                "The provided published time is in the past. Please provide a future date or leave it blank for immediate publication.",
                null
            );
        }

        $isScheduled = $hasPublishedAt && $publishedAt->isFuture();

        if ($isScheduled && $publishedAt->diffInDays(Carbon::now(), true) > 30) {
            throw new AppException(
                "Scheduled Time Too Far in the Future",
                400,
                "Scheduled Time Limit Exceeded",
                "The scheduled publication time is more than 30 days in the future. Please choose a closer date to avoid long queue delays.",
                null
            );
        }

        if ($startDate && $publishedAt->greaterThan(Carbon::parse($startDate))) {
            throw new AppException(
                "Published Time After Event Start",
                400,
                "Invalid Published Time",
                "The event must be published on or before its start date. Please choose an earlier published time.",
                null
            );
        }

        $status = $isScheduled ? 'scheduled' : 'active';

        if ($requestedStatus === 'scheduled' && $status !== 'scheduled') {
            throw new AppException(
                "Invalid Scheduled Configuration",
                400,
                "Scheduled School Event Misconfigured",
                "You specified a scheduled status, but the provided published time is not in the future or is invalid. Please correct the published time.",
                null
            );
        }

        return [$status, $publishedAt];
    }

    public function seedInvitees(object $currentSchool, string $schoolEventId, ResolvedAudienceService $audience): void
    {
        EventAudience::where("school_branch_id", $currentSchool->id)
            ->where("event_id", $schoolEventId)
            ->delete();

        $now = Carbon::now();

        $audience->recipients()->chunk(1000)->each(function ($chunk) use ($currentSchool, $schoolEventId, $now) {
            EventAudience::insert(
                $chunk->map(fn($invitee) => [
                    "id" => Str::uuid()->toString(),
                    "event_id" => $schoolEventId,
                    "school_branch_id" => $currentSchool->id,
                    "actorable_id" => $invitee['id'],
                    "actorable_type" => $invitee['type'],
                    "created_at" => $now,
                    "updated_at" => $now,
                ])->values()->all()
            );
        });
    }

    public function dispatchLifecycleJobs(SchoolEvent $schoolEvent, array $authenticatedUser, object $currentSchool): void
    {
        $schoolEventId = $schoolEvent->id;
        $branchId = $currentSchool->id;
        $publishedAt = Carbon::parse($schoolEvent->published_at);
        $startDate = Carbon::parse($schoolEvent->start_date);
        $endDate = Carbon::parse($schoolEvent->end_date);

        if ($schoolEvent->status === 'active') {
            SendAdminEventCreatedNotificationJob::dispatch($schoolEventId, $authenticatedUser, $branchId)->afterCommit();
            PublishSchoolEventJob::dispatch($branchId, $schoolEventId)->afterCommit();
        } elseif ($schoolEvent->status === 'scheduled') {
            SendAdminScheduledSchoolEventNotiJob::dispatch($schoolEventId, $authenticatedUser, $branchId)->afterCommit();
            PublishSchoolEventJob::dispatch($branchId, $schoolEventId)->delay($publishedAt)->afterCommit();
            SendAdminEventCreatedNotificationJob::dispatch($schoolEventId, $authenticatedUser, $branchId)->delay($publishedAt)->afterCommit();

            if ($publishedAt->greaterThan(now()->addMinutes(10))) {
                SendAdminEventScheduleReminderNotiJob::dispatch($schoolEventId, $authenticatedUser, $branchId)
                    ->delay($publishedAt->copy()->subMinutes(5))
                    ->afterCommit();
            }
        } else {
            return;
        }

        if ($startDate->isFuture()) {
            UpdateSchoolEventStatusJob::dispatch($schoolEventId, $branchId, $authenticatedUser)->delay($startDate)->afterCommit();
        }

        UpdateSchoolEventStatusJob::dispatch($schoolEventId, $branchId, $authenticatedUser)->delay($endDate)->afterCommit();

        AdminActionEvent::dispatch([
            "permissions" => ["schoolAdmin.event.create"],
            "roles" => ["schoolSuperAdmin", "schoolAdmin"],
            "schoolBranch" => $branchId,
            "feature" => "schoolEventManagement",
            "action" => $schoolEvent->status === 'scheduled' ? "schoolEvent.scheduled" : "schoolEvent.published",
            "authAdmin" => $authenticatedUser['authUser'],
            "data" => $schoolEvent,
            "message" => $schoolEvent->status === 'scheduled' ? "School Event Scheduled" : "School Event Published",
        ]);
    }
}
