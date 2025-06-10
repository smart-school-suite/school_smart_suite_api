<?php

namespace App\Services;

use App\Models\SchoolEvent;
use Illuminate\Support\Collection;
use Throwable;

class SchoolEventService
{
     public function getSchoolEvents(object $currentSchool): array
    {
        try {
            $schoolEvents = SchoolEvent::where('school_branch_id', $currentSchool->id)
                                        ->with(['eventCategory', 'eventTag'])
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

    public function getEventDetails($currentSchool, $eventId){
        try{
         $schoolEvents = SchoolEvent::where("school_branch_id", $currentSchool->id)
                            ->with(['eventCategory', 'eventTag'])
                           ->findOrFail($eventId);
            return $schoolEvents;
        }
        catch(Throwable $e){
            throw $e;
        }
    }

    public function deleteEvent($currentSchool, $eventId){
         try{
         $schoolEvents = SchoolEvent::where("school_branch_id", $currentSchool->id)
                            ->with(['eventCategory', 'eventTag'])
                           ->findOrFail($eventId);
            return $schoolEvents;
        }
        catch(Throwable $e){
            throw $e;
        }
    }

    public function getEventByCategory($currentSchool, $categoryId){
        try{
            $schoolEvents = SchoolEvent::where("school_branch_id", $currentSchool->id)
                            ->with(['eventCategory', 'eventTag'])
                            ->where("event_category_id", $categoryId)
                            ->get();
            return $schoolEvents;
        }
        catch(Throwable $e){
            throw $e;
        }
    }

    public function updateEventContent($eventContentData, $eventId, $currentSchool){
        try{
            $event = SchoolEvent::where("school_branch_id", $currentSchool->id)->findOrFail($eventId);
            $cleanedData = array_filter($eventContentData);
            $event->update($cleanedData);
            return $event;
        }
        catch(Throwable $e){
            throw $e;
        }
    }


}
