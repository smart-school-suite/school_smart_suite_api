<?php

namespace App\Services\SchoolEvent;

use App\Models\SchoolEvent;
use App\Models\Student;
use App\Exceptions\AppException;
use App\Models\EventLikeStatus;
use App\Models\EventTag;
use App\Models\EventCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use App\Events\Actions\AdminActionEvent;

class EventService
{
    public function likeSchoolEvent($currentSchool, $authUser, $schoolEventId)
    {
        try {
            $schoolEvent = SchoolEvent::where('school_branch_id', $currentSchool->id)
                ->findOrFail($schoolEventId);
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "School Event with ID '{$schoolEventId}' not found for school branch ID '{$currentSchool->id}'.",
                404,
                "Event Not Found ❌",
                "The event you are trying to like either does not exist or does not belong to this school branch. Please verify the event ID.",
                null
            );
        }

        $likeStatus = EventLikeStatus::where('school_branch_id', $currentSchool->id)
            ->where('likeable_id', $authUser['userId'])
            ->where('likeable_type', $authUser['userType'])
            ->where('event_id', $schoolEventId)
            ->first();

        $isLiked = false;

        if ($likeStatus) {
            if ($likeStatus->status) {
                $schoolEvent->likes = max(0, $schoolEvent->likes - 1);
                $likeStatus->status = false;
                $isLiked = false;
            } else {
                $schoolEvent->likes += 1;
                $likeStatus->status = true;
                $isLiked = true;
            }
            $likeStatus->save();
        } else {
            EventLikeStatus::create([
                'school_branch_id' => $currentSchool->id,
                'likeable_id' => $authUser['userId'],
                'likeable_type' => $authUser['userType'],
                'status' => true,
                'event_id' => $schoolEvent->id,
            ]);

            $schoolEvent->likes += 1;
            $isLiked = true;
        }

        $schoolEvent->save();

        return [
            'event_id' => $schoolEvent->id,
            'likes_count' => $schoolEvent->likes,
            'is_liked' => $isLiked,
        ];
    }
    public function getSchoolEventsByCategory($currentSchool, $authUser, $eventCategoryId)
    {
        try {
            $eventCategory = EventCategory::findOrFail($eventCategoryId);
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Event category with ID '{$eventCategoryId}' not found.",
                404,
                "Category Not Found 🏷️",
                "The event category you requested does not exist. Please check the category ID.",
                null
            );
        }

        $schoolEvents = SchoolEvent::where("school_branch_id", $currentSchool->id)
            ->where("event_category_id", $eventCategoryId)
            ->where("status", "!=", "expired")
            ->with(['eventCategory'])
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

        $likedEventIds = EventLikeStatus::where("school_branch_id", $currentSchool->id)
            ->where("likeable_id", $authUser['userId'])
            ->where("likeable_type", $authUser['userType'])
            ->whereIn("event_id", $schoolEvents->pluck('id'))
            ->where("status", true)
            ->pluck('event_id');

        $schoolEvents = $schoolEvents->map(function ($event) use ($likedEventIds) {
            $event->event_like_status = $likedEventIds->contains($event->id);
            return $event;
        });

        return  $schoolEvents->values()->all();
    }
    public function getSchoolEvents($currentSchool, $authUser)
    {
        $schoolEvents = SchoolEvent::where("school_branch_id", $currentSchool->id)
            ->where("status", "!=", "expired")
            ->with(['eventCategory'])
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

        $likedEventIds = EventLikeStatus::where("school_branch_id", $currentSchool->id)
            ->where("likeable_id", $authUser['userId'])
            ->where("likeable_type", $authUser['userType'])
            ->whereIn("event_id", $schoolEvents->pluck('id'))
            ->where("status", true)
            ->pluck('event_id');

        $schoolEvents = $schoolEvents->map(function ($event) use ($likedEventIds) {
            $event->event_like_status = $likedEventIds->contains($event->id);
            return $event;
        });

        $groupedEvents = $schoolEvents->groupBy(function ($event) {
            return optional($event->eventCategory)->name ?? 'Uncategorized';
        })->map(function ($events, $categoryName) {
            return [
                "category_name" => $categoryName,
                "events" => $events->values()->all()
            ];
        })->values();

        return $groupedEvents;
    }
    public function getExpiredSchoolEvents($currentSchool, $authUser)
    {
        $schoolEvents = SchoolEvent::where("school_branch_id", $currentSchool->id)
            ->where("status",  "expired")
            ->with(['eventCategory'])
            ->get();

        if ($schoolEvents->isEmpty()) {
            throw new AppException(
                "No current or upcoming school events found for school branch ID '{$currentSchool->id}'.",
                404,
                "No Expired School Events Found 📅",
                "We couldn't find any Expired School Events for your school branch.",
                null
            );
        }

        $likedEventIds = EventLikeStatus::where("school_branch_id", $currentSchool->id)
            ->where("likeable_id", $authUser['userId'])
            ->where("likeable_type", $authUser['userType'])
            ->whereIn("event_id", $schoolEvents->pluck('id'))
            ->where("status", true)
            ->pluck('event_id');

        $schoolEvents = $schoolEvents->map(function ($event) use ($likedEventIds) {
            $event->event_like_status = $likedEventIds->contains($event->id);
            return $event;
        });

        $groupedEvents = $schoolEvents->groupBy(function ($event) {
            return optional($event->eventCategory)->name ?? 'Uncategorized';
        })->map(function ($events, $categoryName) {
            return [
                "category_name" => $categoryName,
                "events" => $events->values()->all()
            ];
        })->values();

        return $groupedEvents;
    }
    public function getExpiredSchoolEventsByCategory($currentSchool, $authUser, $eventCategoryId)
    {
        try {
            $eventCategory = EventCategory::where("school_branch_id", $currentSchool->id)
                ->findOrFail($eventCategoryId);
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Event category with ID '{$eventCategoryId}' not found.",
                404,
                "Category Not Found 🏷️",
                "The event category you requested does not exist. Please check the category ID.",
                null
            );
        }

        $schoolEvents = SchoolEvent::where("school_branch_id", $currentSchool->id)
            ->where("event_category_id", $eventCategoryId)
            ->where("status", "expired")
            ->with(['eventCategory'])
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

        $likedEventIds = EventLikeStatus::where("school_branch_id", $currentSchool->id)
            ->where("likeable_id", $authUser['userId'])
            ->where("likeable_type", $authUser['userType'])
            ->whereIn("event_id", $schoolEvents->pluck('id'))
            ->where("status", true)
            ->pluck('event_id');

        $schoolEvents = $schoolEvents->map(function ($event) use ($likedEventIds) {
            $event->event_like_status = $likedEventIds->contains($event->id);
            return $event;
        });

        return  $schoolEvents->values()->all();
    }
    public function getScheduledSchoolEventsByCategory($currentSchool, $eventCategoryId)
    {
        try {
            $eventCategory = EventCategory::where("school_branch_id", $currentSchool->id)
                ->findOrFail($eventCategoryId);
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Event category with ID '{$eventCategoryId}' not found.",
                404,
                "Category Not Found 🏷️",
                "The event category you requested does not exist. Please check the category ID.",
                null
            );
        }

        $schoolEvents = SchoolEvent::where("school_branch_id", $currentSchool->id)
            ->where("event_category_id", $eventCategoryId)
            ->where("status", "scheduled")
            ->with(['eventCategory'])
            ->get();

        if ($schoolEvents->isEmpty()) {
            throw new AppException(
                "No current or upcoming '{$eventCategory->name}' events found for school branch ID '{$currentSchool->id}'.",
                404,
                "No Active School Events Found 📅 For this category",
                "We couldn't find any events scheduled under the '{$eventCategory->name}' category. Please ensure events have been created and their status set to scheduled'.",
                null
            );
        }

        return $schoolEvents->values()->all();
    }
    public function getScheduledSchoolEvents($currentSchool)
    {
        $schoolEvents = SchoolEvent::where("school_branch_id", $currentSchool->id)
            ->where("status", "scheduled")
            ->with(['eventCategory'])
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

        $groupedEvents = $schoolEvents->groupBy(function ($event) {
            return optional($event->eventCategory)->name ?? 'Uncategorized';
        })->map(function ($events, $categoryName) {
            return [
                "category_name" => $categoryName,
                "events" => $events->values()->all()
            ];
        })->values();

        return $groupedEvents;
    }
    public function getDraftSchoolEvents($currentSchool)
    {
        $schoolEvents = SchoolEvent::where("school_branch_id", $currentSchool->id)
            ->where("status", "draft")
            ->with(['eventCategory'])
            ->get();
        if ($schoolEvents->isEmpty()) {
            throw new AppException(
                "No draft school events found for school branch ID '{$currentSchool->id}'.",
                404,
                "No Draft School Events Found 📅",
                "We couldn't find any draft events scheduled for your school branch. Please ensure that events have been created and their status is set to 'draft'.",
                null
            );
        }
        $groupedEvents = $schoolEvents->groupBy(function ($event) {
            return optional($event->eventCategory)->name ?? 'Uncategorized';
        })->map(function ($events, $categoryName) {
            return [
                "category_name" => $categoryName,
                "events" => $events->values()->all()
            ];
        })->values();

        return $groupedEvents;
    }
    public function getDraftSchoolEventsByCategory($currentSchool, $eventCategoryId)
    {
        try {
            $eventCategory = EventCategory::where("school_branch_id", $currentSchool->id)
                ->findOrFail($eventCategoryId);
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Event category with ID '{$eventCategoryId}' not found.",
                404,
                "Category Not Found 🏷️",
                "The event category you requested does not exist. Please check the category ID.",
                null
            );
        }

        $schoolEvents = SchoolEvent::where("school_branch_id", $currentSchool->id)
            ->where("event_category_id", $eventCategoryId)
            ->where("status", "draft")
            ->with(['eventCategory'])
            ->get();

        if ($schoolEvents->isEmpty()) {
            throw new AppException(
                "No draft '{$eventCategory->name}' events found for school branch ID '{$currentSchool->id}'.",
                404,
                "No Draft School Events Found 📅 For this category",
                "We couldn't find any draft events scheduled under the '{$eventCategory->name}' category. Please ensure events have been created and their status is set to 'draft'.",
                null
            );
        }

        return $schoolEvents->values()->all();
    }
    public function deleteSchoolEvent($currentSchool, $eventId, $authAdmin)
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

        $schoolEvent->delete();
                AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.event.create"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "schoolEventManagement",
                "authAdmin" => $authAdmin,
                "data" => $schoolEvent,
                "message" => "School Event Deleted",
            ]
        );
        return $schoolEvent;
    }
    public function getSchoolEventDetails($currentSchool, $eventId)
    {
        $schoolEvents = SchoolEvent::where("school_branch_id", $currentSchool->id)
            ->with(['eventCategory'])
            ->find($eventId);

        if (!$schoolEvents) {
            throw new AppException(
                "School event ID '{$eventId}' not found for school branch ID '{$currentSchool->id}'.",
                404,
                "Event Details Not Found 🔍",
                "We couldn't find the specific details for the event you requested. Please verify the Event ID is correct and belongs to your school branch.",
                null
            );
        }

        return $schoolEvents;
    }
    public function updateSchoolEventContent($eventData, $currentSchool, $eventId, $authAdmin)
    {
        $schoolEvents = SchoolEvent::where("school_branch_id", $currentSchool->id)
            ->find($eventId);

        if (!$schoolEvents) {
            throw new AppException(
                "School Event Not Found",
                404,
                "Event Not Found",
                "The specified school event does not exist or does not belong to the current school.",
                null
            );
        }

        $dataToUpdate = array_filter($eventData, function ($value) {
            return !is_null($value) && $value !== '';
        });

        if (isset($dataToUpdate['tag_ids'])) {
            if (!empty($dataToUpdate['tag_ids'])) {
                $tags = $this->getTags($dataToUpdate);
                $dataToUpdate['tags'] = json_encode($tags->toArray());
            } else {
                $dataToUpdate['tags'] = '[]';
            }
        }

        if (isset($dataToUpdate['background_image'])) {
            if ($schoolEvents->background_image) {
                Storage::disk('public')->delete('school_events/' . $schoolEvents->background_image);
            }

            if ($dataToUpdate['background_image'] instanceof \Illuminate\Http\UploadedFile) {
                $fileName = time() . '.' . $dataToUpdate['background_image']->getClientOriginalExtension();
                $dataToUpdate['background_image']->storeAs('public/school_events', $fileName);
                $dataToUpdate['background_image'] = $fileName;
            } else {
                $dataToUpdate['background_image'] = '';
            }
        }

        $schoolEvents->update($dataToUpdate);
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.event.update"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "schoolEventManagement",
                "authAdmin" => $authAdmin,
                "data" => $schoolEvents,
                "message" => "School Event Updated",
            ]
        );
        return $schoolEvents;
    }
    protected function getTags(array $data): Collection
    {
        if (empty($data['tag_ids'])) {
            return collect();
        }

        $tagIds = collect($data['tag_ids'])->pluck('tag_id')->unique()->toArray();
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
    public function getStudentUpcomingEvents($currentSchool, $student)
    {
        $now = Carbon::now();
        $studentSpecialtyId = $student->specialty_id;

        $events = SchoolEvent::where('school_branch_id', $currentSchool->id)
            ->where('status', 'active')
            ->where('end_date', '>=', $now)
            ->orderBy('start_date', 'asc')
            ->get();

        $visibleEvents = $events->filter(function ($event) use ($studentSpecialtyId) {
            $audience = $event->audience;

            if (empty($audience->students) && empty($audience->teachers) && empty($audience->admins)) {
                return true;
            }

            if (!empty($audience->students) && is_array($audience->students)) {
                return in_array($studentSpecialtyId, $audience->students);
            }

            return false;
        });

        $likedEventIds = EventLikeStatus::where('likeable_type', Student::class)
            ->where('likeable_id', $student->id)
            ->where('event_id', $visibleEvents->pluck('id')->toArray())
            ->where('status', true)
            ->pluck('event_id')
            ->toArray();

        $formatted = $visibleEvents->map(function ($event) use ($likedEventIds) {
            return [
                "event_id"        => $event->id,
                "title"           => $event->title,
                "description"     => $event->description ?? "No description available",
                "background_image" => $event->background_image,
                "location"        => $event->location,
                "organizer"       => $event->organizer,
                "start_date"      => $event->start_date,
                "end_date"        => $event->end_date,
                "tags"            => $event->tags ?? [],
                "likes_count"     => $event->likes ?? 0,
                "is_liked"        => in_array($event->id, $likedEventIds)
            ];
        })->values();

        return $formatted;
    }
}
