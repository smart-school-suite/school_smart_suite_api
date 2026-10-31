<?php

namespace App\Services\SchoolEvent;

use App\Exceptions\AppException;
use App\Models\SchoolEvent\SchoolEventCategory;
use App\Models\SchoolEvent\SchoolEventLike;
use App\Models\SchoolEvent\SchoolEvent;
use App\Services\Asset\SchoolEventImageService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class EventService
{
    private const CONTENT_FIELDS = ['title', 'description', 'organizer', 'location', 'start_date', 'end_date', 'event_category_id'];

    public function __construct(
        private EventPublishingService $publishing,
        private SchoolEventImageService $imageService
    ) {}

    public function likeSchoolEvent(object $currentSchool, array $authUser, string $schoolEventId)
    {
        $schoolEvent = SchoolEvent::where('school_branch_id', $currentSchool->id)
            ->whereIn('status', ['active', 'expired'])
            ->find($schoolEventId);

        if (!$schoolEvent) {
            throw new AppException(
                "School Event with ID '{$schoolEventId}' not found for school branch ID '{$currentSchool->id}'.",
                404,
                "Event Not Found ❌",
                "The event you are trying to like either does not exist, is not published yet, or does not belong to this school branch. Please verify the event ID.",
                null
            );
        }

        $isLiked = DB::transaction(function () use ($currentSchool, $authUser, $schoolEvent) {
            $deleted = SchoolEventLike::where('school_branch_id', $currentSchool->id)
                ->where('event_id', $schoolEvent->id)
                ->where('likeable_id', $authUser['userId'])
                ->where('likeable_type', $authUser['userType'])
                ->delete();

            if ($deleted > 0) {
                return false;
            }

            SchoolEventLike::create([
                'school_branch_id' => $currentSchool->id,
                'event_id' => $schoolEvent->id,
                'likeable_id' => $authUser['userId'],
                'likeable_type' => $authUser['userType'],
            ]);

            return true;
        });

        return [
            'event_id' => $schoolEvent->id,
            'likes_count' => SchoolEventLike::where('event_id', $schoolEvent->id)->count(),
            'is_liked' => $isLiked,
        ];
    }

    public function getSchoolEventsByCategory(object $currentSchool, array $authUser, string $eventCategoryId)
    {
        $eventCategory = $this->findCategory($currentSchool, $eventCategoryId);

        $schoolEvents = $this->eventQuery($currentSchool, $authUser)
            ->where("event_category_id", $eventCategoryId)
            ->whereIn("status", ['active', 'scheduled'])
            ->orderBy('start_date')
            ->get();

        if ($schoolEvents->isEmpty()) {
            throw new AppException(
                "No current or upcoming '{$eventCategory->name}' events found for school branch ID '{$currentSchool->id}'.",
                404,
                "No Active School Events Found 📅 For this category",
                "We couldn't find any current or upcoming events scheduled under the '{$eventCategory->name}' category. Please ensure events have been created and their status is not set to 'expired'.",
                null
            );
        }

        return $schoolEvents->values()->all();
    }

    public function getSchoolEvents(object $currentSchool, array $authUser)
    {
        $schoolEvents = $this->eventQuery($currentSchool, $authUser)
            ->whereIn("status", ['active', 'scheduled'])
            ->orderBy('start_date')
            ->get();

        if ($schoolEvents->isEmpty()) {
            throw new AppException(
                "No current or upcoming school events found for school branch ID '{$currentSchool->id}'.",
                404,
                "No Active School Events Found 📅",
                "We couldn't find any current or upcoming events scheduled for your school branch. Please ensure that events have been created and their status is not set to 'expired'.",
                null
            );
        }

        return $this->groupByCategory($schoolEvents);
    }

    public function getExpiredSchoolEvents(object $currentSchool, array $authUser)
    {
        $schoolEvents = $this->eventQuery($currentSchool, $authUser)
            ->where("status", "expired")
            ->orderByDesc('end_date')
            ->get();

        if ($schoolEvents->isEmpty()) {
            throw new AppException(
                "No expired school events found for school branch ID '{$currentSchool->id}'.",
                404,
                "No Expired School Events Found 📅",
                "We couldn't find any Expired School Events for your school branch.",
                null
            );
        }

        return $this->groupByCategory($schoolEvents);
    }

    public function getExpiredSchoolEventsByCategory(object $currentSchool, array $authUser, string $eventCategoryId)
    {
        $eventCategory = $this->findCategory($currentSchool, $eventCategoryId);

        $schoolEvents = $this->eventQuery($currentSchool, $authUser)
            ->where("event_category_id", $eventCategoryId)
            ->where("status", "expired")
            ->orderByDesc('end_date')
            ->get();

        if ($schoolEvents->isEmpty()) {
            throw new AppException(
                "No Expired '{$eventCategory->name}' events found for school branch ID '{$currentSchool->id}'.",
                404,
                "No Expired School Events Found 📅 For this category",
                "We couldn't find any expired school events  under the '{$eventCategory->name}' category.",
                null
            );
        }

        return $schoolEvents->values()->all();
    }

    public function getScheduledSchoolEventsByCategory(object $currentSchool, string $eventCategoryId)
    {
        $eventCategory = $this->findCategory($currentSchool, $eventCategoryId);

        $schoolEvents = $this->eventQuery($currentSchool)
            ->where("event_category_id", $eventCategoryId)
            ->where("status", "scheduled")
            ->orderBy('published_at')
            ->get();

        if ($schoolEvents->isEmpty()) {
            throw new AppException(
                "No scheduled '{$eventCategory->name}' events found for school branch ID '{$currentSchool->id}'.",
                404,
                "No Scheduled School Events Found 📅 For this category",
                "We couldn't find any events scheduled under the '{$eventCategory->name}' category. Please ensure events have been created and their status set to scheduled.",
                null
            );
        }

        return $schoolEvents->values()->all();
    }

    public function getScheduledSchoolEvents(object $currentSchool)
    {
        $schoolEvents = $this->eventQuery($currentSchool)
            ->where("status", "scheduled")
            ->orderBy('published_at')
            ->get();

        if ($schoolEvents->isEmpty()) {
            throw new AppException(
                "No Scheduled School Events found for school branch ID '{$currentSchool->id}'.",
                404,
                "No Scheduled School Events Found 📅",
                "We couldn't find any events scheduled for your school branch. Please ensure that events have been created and their status set to scheduled.",
                null
            );
        }

        return $this->groupByCategory($schoolEvents);
    }

    public function getDraftSchoolEvents(object $currentSchool)
    {
        $schoolEvents = $this->eventQuery($currentSchool)
            ->where("status", "draft")
            ->latest()
            ->get();

        if ($schoolEvents->isEmpty()) {
            throw new AppException(
                "No draft school events found for school branch ID '{$currentSchool->id}'.",
                404,
                "No Draft School Events Found 📅",
                "We couldn't find any draft events for your school branch. Please ensure that events have been created and their status is set to 'draft'.",
                null
            );
        }

        return $this->groupByCategory($schoolEvents);
    }

    public function getDraftSchoolEventsByCategory(object $currentSchool, string $eventCategoryId)
    {
        $eventCategory = $this->findCategory($currentSchool, $eventCategoryId);

        $schoolEvents = $this->eventQuery($currentSchool)
            ->where("event_category_id", $eventCategoryId)
            ->where("status", "draft")
            ->latest()
            ->get();

        if ($schoolEvents->isEmpty()) {
            throw new AppException(
                "No draft '{$eventCategory->name}' events found for school branch ID '{$currentSchool->id}'.",
                404,
                "No Draft School Events Found For this category",
                "We couldn't find any draft events under the '{$eventCategory->name}' category. Please ensure events have been created and their status is set to 'draft'.",
                null
            );
        }

        return $schoolEvents->values()->all();
    }

    public function deleteSchoolEvent(object $currentSchool, string $eventId, $authAdmin)
    {
        $schoolEvent = SchoolEvent::where("school_branch_id", $currentSchool->id)
            ->find($eventId);

        if (!$schoolEvent) {
            throw new AppException(
                "School event ID '{$eventId}' not found or does not belong to school branch ID '{$currentSchool->id}'.",
                404,
                "Event Not Found for Deletion 🗑️",
                "We couldn't find the specific event you're trying to delete. Please verify the Event ID and confirm it belongs to your current school branch.",
                null
            );
        }

        DB::transaction(function () use ($currentSchool, $schoolEvent) {
            $this->imageService->removeAllImages($schoolEvent, $currentSchool->id);
            $schoolEvent->delete();
        });

        return $schoolEvent;
    }

    public function getSchoolEventDetails(object $currentSchool, string $eventId, ?array $authUser = null)
    {
        $schoolEvent = $this->eventQuery($currentSchool, $authUser)
            ->withCount('invitees as invitee_count')
            ->find($eventId);

        if (!$schoolEvent) {
            throw new AppException(
                "School event ID '{$eventId}' not found for school branch ID '{$currentSchool->id}'.",
                404,
                "Event Details Not Found 🔍",
                "We couldn't find the specific details for the event you requested. Please verify the Event ID is correct and belongs to your school branch.",
                null
            );
        }

        return $schoolEvent;
    }

    public function updateSchoolEventContent(array $eventData, object $currentSchool, string $eventId, $authAdmin)
    {
        $uploadedImages = collect();

        try {
            return DB::transaction(function () use ($eventData, $currentSchool, $eventId, &$uploadedImages) {
                $schoolEvent = SchoolEvent::where("school_branch_id", $currentSchool->id)
                    ->lockForUpdate()
                    ->find($eventId);

                if (!$schoolEvent) {
                    throw new AppException(
                        "School Event Not Found",
                        404,
                        "Event Not Found",
                        "The specified school event does not exist or does not belong to the current school.",
                        null
                    );
                }

                if ($schoolEvent->status === 'expired') {
                    throw new AppException(
                        "Expired school event {$eventId} cannot be edited.",
                        400,
                        "Event Already Ended",
                        "This event has already ended, so its content can no longer be changed.",
                        null
                    );
                }

                $updates = array_filter(
                    array_intersect_key($eventData, array_flip(self::CONTENT_FIELDS)),
                    fn($value) => !is_null($value) && $value !== ''
                );

                if (array_key_exists('tag_ids', $eventData)) {
                    $updates['tags'] = $this->publishing->getTags($eventData)->toArray();
                }

                $schoolEvent->fill($updates);

                if (Carbon::parse($schoolEvent->end_date)->lessThan(Carbon::parse($schoolEvent->start_date))) {
                    throw new AppException(
                        "Event end date is before its start date.",
                        422,
                        "Invalid Event Dates",
                        "The event end date must be on or after its start date.",
                        null
                    );
                }

                if ($schoolEvent->isDirty('end_date')) {
                    $schoolEvent->expires_at = $schoolEvent->end_date;
                }

                $schoolEvent->save();

                $this->imageService->removeImages($schoolEvent, $currentSchool->id, $eventData['remove_image_ids'] ?? []);
                $uploadedImages = $this->imageService->uploadImages($schoolEvent, $eventData['images'] ?? [], $currentSchool->id);

                return $schoolEvent->load(['currentImages', 'eventCategory']);
            });
        } catch (Throwable $exception) {
            $this->imageService->deleteObjects($uploadedImages->pluck('object_key')->all());
            throw $exception;
        }
    }

    public function getStudentUpcomingEvents(object $currentSchool, object $student)
    {
        $authUser = ['userId' => $student->id, 'userType' => get_class($student)];

        return $this->eventQuery($currentSchool, $authUser)
            ->where('status', 'active')
            ->where('end_date', '>=', Carbon::now())
            ->whereHas('invitees', function (Builder $query) use ($student) {
                $query->where('actorable_id', $student->id)
                    ->where('actorable_type', $student->getMorphClass());
            })
            ->orderBy('start_date')
            ->get()
            ->map(fn($event) => [
                "event_id" => $event->id,
                "title" => $event->title,
                "description" => $event->description ?? "No description available",
                "images" => $event->currentImages->map(fn($image) => [
                    "id" => $image->id,
                    "object_key" => $image->object_key,
                    "width" => $image->width,
                    "height" => $image->height,
                ])->values(),
                "location" => $event->location,
                "organizer" => $event->organizer,
                "start_date" => $event->start_date,
                "end_date" => $event->end_date,
                "category" => $event->eventCategory?->name,
                "tags" => $event->tags ?? [],
                "likes_count" => $event->likes_count ?? 0,
                "is_liked" => (bool) ($event->is_liked ?? false),
            ])
            ->values();
    }

    private function eventQuery(object $currentSchool, ?array $authUser = null): Builder
    {
        $query = SchoolEvent::query()
            ->where("school_branch_id", $currentSchool->id)
            ->with(['eventCategory', 'currentImages'])
            ->withCount('schoolEventLikes as likes_count');

        if (!empty($authUser['userId'])) {
            $query->withExists(['schoolEventLikes as is_liked' => function (Builder $likes) use ($authUser) {
                $likes->where('likeable_id', $authUser['userId'])
                    ->where('likeable_type', $authUser['userType']);
            }]);
        }

        return $query;
    }

    private function findCategory(object $currentSchool, string $eventCategoryId): SchoolEventCategory
    {
        $eventCategory = SchoolEventCategory::where("school_branch_id", $currentSchool->id)->find($eventCategoryId);

        if (!$eventCategory) {
            throw new AppException(
                "Event category with ID '{$eventCategoryId}' not found.",
                404,
                "Category Not Found 🏷️",
                "The event category you requested does not exist. Please check the category ID.",
                null
            );
        }

        return $eventCategory;
    }

    private function groupByCategory(Collection $schoolEvents): Collection
    {
        return $schoolEvents
            ->groupBy(fn($event) => $event->eventCategory?->name ?? 'Uncategorized')
            ->map(fn($events, $categoryName) => [
                "category_name" => $categoryName,
                "events" => $events->values()->all(),
            ])
            ->values();
    }
}
