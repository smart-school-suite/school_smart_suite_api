<?php

namespace App\Services;

use App\Models\Events;

class EventsService
{
    // Implement your logic here
    public function createEvent(array $data, $currentSchool)
    {
        $event = new Events();
        $event->school_branch_id = $currentSchool->id;
        $event->title = $data["title"];
        $event->start_date = $data["start_date"];
        $event->end_date = $data["end_date"];
        $event->location = $data["location"];
        $event->description = $data["description"];
        $event->organizer = $data["organizer"];
        $event->category = $data["category"];
        $event->duration = $data["duration"];
        $event->save();
        return $event;
    }

    public function updateEvent(array $data, $currentSchool, $event_id)
    {
        $find_event = Events::where("school_branch_id", $currentSchool->id)->find($event_id);
        if (!$find_event) {
            return ApiResponseService::error("Event not found", null, 404);
        }
        $filterData = array_filter($data);
        $find_event->update($filterData);
        return $find_event;
    }

    public function deleteEvent(string $event_id)
    {
        $find_event = Events::find($event_id);
        if (!$find_event) {
            return ApiResponseService::error("Event not found", null, 404);
        }

        $find_event->delete();

        return $find_event;
    }

    public function getEvents($currentSchool)
    {
        $events = Events::where("school_branch_id", $currentSchool->id)->get();
        return $events;
    }

    public function eventDetails(string $event_id, $currentSchool)
    {
        $eventExist = Events::where("school_branch_id", $currentSchool->id)->find($event_id);
        if (!$eventExist) {
            return ApiResponseService::error("Event not found", null, 404);
        }
        return $eventExist;
    }
}
