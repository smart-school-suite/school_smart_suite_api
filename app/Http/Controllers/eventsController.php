<?php

namespace App\Http\Controllers;

use App\Models\Events;
use App\Http\Requests\EventsRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Services\ApiResponseService;
use App\Services\EventsService;
use Illuminate\Http\Request;

class eventsController extends Controller
{
    //
    protected EventsService $eventsService;
    public function __construct(EventsService $eventsService)
    {
        $this->eventsService = $eventsService;
    }
    public function create_school_event(EventsRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $createElection = $this->eventsService->createEvent($request->validated(), $currentSchool);
        return ApiResponseService::success("Events Created Succefully", $createElection, null, 201);
    }

    public function update_school_event(UpdateEventRequest $request, $event_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateEvent = $this->eventsService->updateEvent($request->validated(), $currentSchool, $event_id);
        return ApiResponseService::success('Event updated succefully', $updateEvent, null, 200);
    }

    public function delete_school_event(Request $request, $event_id)
    {
        $deleteEvent = $this->eventsService->deleteEvent($event_id);
        return ApiResponseService::success('Event Deleted Successfully', $deleteEvent, null, 200);
    }
    public function get_all_events(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getEvents = $this->eventsService->getEvents($currentSchool);
        return ApiResponseService::success('Events fetched successfully', $getEvents, null, 200);
    }

    public function event_details(Request $request, $event_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $eventDetails = $this->eventsService->eventDetails($currentSchool, $event_id);
        return ApiResponseService::success('Event Details Fetched Succefully', $eventDetails, null, 200);
    }
}
