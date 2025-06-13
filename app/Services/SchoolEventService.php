<?php

namespace App\Services;

use App\Models\SchoolEvent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SchoolEventService
{
    public function getSchoolEvents(object $currentSchool): array
    {
        try {
            $schoolEvents = SchoolEvent::where('school_branch_id', $currentSchool->id)
                ->with(['eventCategory', 'eventTag'])
                ->where("status", "!=", "draft")
                ->get();

            $categorizedEvents = $schoolEvents->groupBy(function ($event) {
                return $event->eventCategory->name ?? 'Uncategorized';
            })->map(function (Collection $events) {
                return $events->map(function ($event) {
                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                        'description' => $event->description,
                        'organizer' => $event->organizer,
                        'location' => $event->location,
                        'start_date' => $event->start_date,
                        'end_date' => $event->end_date,
                        'status' => $event->status,
                        'background_image' => $event->background_image,
                        'tag_name' => $event->eventTag->name ?? null,
                    ];
                })->toArray();
            })->toArray();

            return $categorizedEvents;
        } catch (Throwable $e) {
            report($e);
            throw $e;
        }
    }

    public function getEventDetails($currentSchool, $eventId)
    {
        try {
            $schoolEvents = SchoolEvent::where("school_branch_id", $currentSchool->id)
                ->with(['eventCategory', 'eventTag'])
                ->findOrFail($eventId);
            return $schoolEvents;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function deleteEvent($currentSchool, $eventId)
    {
        try {
            $schoolEvents = SchoolEvent::where("school_branch_id", $currentSchool->id)
                ->with(['eventCategory', 'eventTag'])
                ->findOrFail($eventId);
            return $schoolEvents;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function getEventByCategory($currentSchool, $categoryId)
    {
        try {
            $schoolEvents = SchoolEvent::where("school_branch_id", $currentSchool->id)
                ->with(['eventCategory', 'eventTag'])
                ->where("event_category_id", $categoryId)
                ->get();
            return $schoolEvents;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function updateEventContent($eventContentData, $eventId, $currentSchool, $eventImage)
    {
        try {
            DB::beginTransaction();
            $event = SchoolEvent::where("school_branch_id", $currentSchool->id)->findOrFail($eventId);
            $cleanedData = array_filter($eventContentData, function ($value) {
                return $value !== null;
            });
            $event->update($cleanedData);
            if($eventContentData['end_date'] && $event->status != 'expired'){
              $event->expires_at = $eventContentData['end_date'];
              $event->save();
            }
            if($eventImage){
                 $newImagePath = $this->handlePictureUpload($eventImage, $event);

            if ($newImagePath !== null || ($eventImage === null && $event->background_image !== null)) {
                $event->background_image = $newImagePath;
                $event->save();
            }
            }
            DB::commit();
            return $event;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    private function handlePictureUpload($eventImage, $event)
    {
        if ($event->background_image) {
            Storage::disk('public')->delete('EventBackgroundImg/' . $event->background_image);
        }
        if ($eventImage) {
            $fileName = time() . '_' . uniqid() . '.' . $eventImage->getClientOriginalExtension(); // Added uniqid for better uniqueness
            $eventImage->storeAs('public/EventBackgroundImg', $fileName);
            return $fileName;
        }
        return null;
    }

    public function getEventsByStatus($currentSchool, $status)
    {
        try {
            $events = SchoolEvent::where("school_branch_id", $currentSchool->id)
                ->where("status", $status)
                ->get();
            return $events;
        } catch (Throwable $e) {
            throw $e;
        }
    }
}
