<?php

namespace App\Services\SchoolEvent;

use App\Exceptions\AppException;
use App\Models\SchoolEvent\SchoolEventAuthor;
use App\Models\SchoolEvent\SchoolEvent;
use App\Services\Asset\SchoolEventImageService;
use App\Services\Audience\ResolvedAudienceService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateEventService
{
    public function __construct(
        private EventPublishingService $publishing,
        private SchoolEventImageService $imageService
    ) {}

    public function createSchoolEvent(object $currentSchool, array $authenticatedUser, array $data)
    {
        $isDraft = ($data['status'] ?? null) === 'draft';
        $tags = $this->publishing->getTags($data);

        if (!$isDraft && Carbon::parse($data['end_date'])->isPast()) {
            throw new AppException(
                "Event End Date In The Past",
                400,
                "Invalid End Date",
                "The event end date has already passed. Please choose an end date in the future.",
                null
            );
        }

        $audience = $isDraft ? null : $this->publishing->resolveAudience($currentSchool, $data);

        return $this->createSchoolEventContent($currentSchool, $authenticatedUser, $data, $tags, $audience);
    }

    public function createSchoolEventContent(
        object $currentSchool,
        array $authenticatedUser,
        array $data,
        Collection $tags,
        ?ResolvedAudienceService $audience = null
    ) {
        [$status, $publishedAt] = $this->publishing->resolveTiming(
            $data['status'] ?? null,
            $data['published_at'] ?? null,
            $data['start_date'] ?? null
        );

        $uploadedImages = collect();

        try {
            return DB::transaction(function () use ($currentSchool, $authenticatedUser, $data, $tags, $audience, $status, $publishedAt, &$uploadedImages) {
                $schoolEvent = SchoolEvent::create([
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'location' => $data['location'],
                    'organizer' => $data['organizer'],
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'status' => $status,
                    'published_at' => $publishedAt,
                    'expires_at' => $data['end_date'],
                    'event_category_id' => $data['event_category_id'],
                    'notification_sent_at' => null,
                    'audience' => $this->publishing->audiencePayload($data),
                    'tags' => $tags->toArray(),
                    'school_branch_id' => $currentSchool->id,
                ]);

                if ($audience && !$audience->isEmpty()) {
                    $this->publishing->seedInvitees($currentSchool, $schoolEvent->id, $audience);
                }

                SchoolEventAuthor::create([
                    'school_branch_id' => $currentSchool->id,
                    'actorable_id' => $authenticatedUser['userId'],
                    'actorable_type' => $authenticatedUser['userType'],
                    'event_id' => $schoolEvent->id,
                ]);

                $uploadedImages = $this->imageService->uploadImages($schoolEvent, $data['images'] ?? [], $currentSchool->id);

                $this->publishing->dispatchScheduleNotifications($schoolEvent, $authenticatedUser, $currentSchool);

                return $schoolEvent->load(['currentImages', 'eventCategory']);
            });
        } catch (Throwable $exception) {
            $this->imageService->deleteObjects($uploadedImages->pluck('object_key')->all());
            throw $exception;
        }
    }
}
