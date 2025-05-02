<?php

namespace App\Http\Controllers;

use App\Services\ApiResponseService;
use App\Http\Requests\Events\CreateEventRequest;
use App\Http\Requests\Events\BulkUpdateEventRequest;
use App\Http\Requests\Events\UpdateEventRequest;
use App\Services\EventsService;
use Illuminate\Http\Request;

class EventsController extends Controller
{
    //
    protected EventsService $eventsService;
    public function __construct(EventsService $eventsService)
    {
        $this->eventsService = $eventsService;
    }
    public function createEvent(CreateEventRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $createElection = $this->eventsService->createEvent($request->all(), $currentSchool);
        return ApiResponseService::success("Events Created Succefully", $createElection, null, 201);
    }

    public function updateEvent(UpdateEventRequest $request, $event_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateEvent = $this->eventsService->updateEvent($request->validated(), $currentSchool, $event_id);
        return ApiResponseService::success('Event updated succefully', $updateEvent, null, 200);
    }

    public function deleteEvent(Request $request, $event_id)
    {
        $deleteEvent = $this->eventsService->deleteEvent($event_id);
        return ApiResponseService::success('Event Deleted Successfully', $deleteEvent, null, 200);
    }
    public function getEvents(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getEvents = $this->eventsService->getEvents($currentSchool);
        return ApiResponseService::success('Events fetched successfully', $getEvents, null, 200);
    }

    public function getEventDetails(Request $request, $event_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $eventDetails = $this->eventsService->eventDetails($currentSchool, $event_id);
        return ApiResponseService::success('Event Details Fetched Succefully', $eventDetails, null, 200);
    }
}
