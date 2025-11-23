<?php

namespace App\Http\Controllers\SchoolEvent;

use App\Http\Controllers\Controller;
use App\Http\Requests\Event\CreateEventRequest;
use App\Http\Requests\Event\UpdateEventContentRequest;
use App\Http\Requests\Event\UpdateEventDraftRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\SchoolEvent\CreateEventService;
use App\Services\SchoolEvent\EventService;
use App\Services\SchoolEvent\UpdateDraftEventStatusService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class SchoolEventController extends Controller
{
    protected CreateEventService $createEventService;
    protected EventService $eventService;

    protected UpdateDraftEventStatusService $updateDraftEventStatusService;

    public function __construct(
        CreateEventService $createEventService,
        EventService $eventService,
        UpdateDraftEventStatusService $updateDraftEventStatusService
    ) {
        $this->createEventService = $createEventService;
        $this->eventService = $eventService;
        $this->updateDraftEventStatusService = $updateDraftEventStatusService;
    }

    public function likeSchoolEvent(Request $request, $schoolEventId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $this->eventService->likeSchoolEvent($currentSchool, $this->getAuthenticatedUser(), $schoolEventId);
        return ApiResponseService::success("Event Liked Successfully", null, null, 200);
    }
    public function getSchoolEventByCategory(Request $request, $eventCategoryId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $schoolEvents = $this->eventService->getSchoolEventsByCategory($currentSchool, $this->getAuthenticatedUser(), $eventCategoryId,);
        return ApiResponseService::success("Events Fetched Successfully", $schoolEvents, null, 200);
    }
    public function createSchoolEvent(CreateEventRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $createSchoolEvent = $this->createEventService->createSchoolEvent($currentSchool, $this->getAuthenticatedUser(), $request->validated());
        return ApiResponseService::success("School Event Created Successfully", $createSchoolEvent, null, 201);
    }
    public function updateDraftSchoolEvent(UpdateEventDraftRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateDraftSchoolEvent = $this->updateDraftEventStatusService->updateDraftSchoolEvent($currentSchool, $this->getAuthenticatedUser(), $request->validated());
        return ApiResponseService::success("Draft School Event Updated Successfully", $updateDraftSchoolEvent, null, 200);
    }
    public function updateSchoolEventContent(UpdateEventContentRequest $request, $schoolEventId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateEventContent = $this->eventService->updateSchoolEventContent($request->validated(), $currentSchool, $schoolEventId);
        return ApiResponseService::success("School Event Content Updated Successfully", $updateEventContent, null, 200);
    }
    public function deleteSchoolEventContent(Request $request, $schoolEventId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $this->eventService->deleteSchoolEvent($currentSchool, $schoolEventId);
        return ApiResponseService::success("School Event Deleted Successfully", null, null, 200);
    }
    public function getSchoolEventDetails(Request $request, $schoolEventId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $schoolEventDetails = $this->eventService->getSchoolEventDetails($currentSchool, $schoolEventId);
        return ApiResponseService::success("School Event Details Fetched Successfully", $schoolEventDetails, null, 200);
    }
    public function getExpiredSchoolEvents(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $schoolEvents = $this->eventService->getExpiredSchoolEvents($currentSchool, $this->getAuthenticatedUser());
        return ApiResponseService::success("Expired School Events Fetched Successfully", $schoolEvents, null, 200);
    }
    public function getExpiredSchoolEventsByCategory(Request $request, $eventCategoryId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $schoolEvents = $this->eventService->getExpiredSchoolEventsByCategory($currentSchool, $this->getAuthenticatedUser(), $eventCategoryId);
        return ApiResponseService::success("Expired Events Fetched Successfully", $schoolEvents, null, 200);
    }
    public function getSchoolEvents(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $schoolEvents = $this->eventService->getSchoolEvents($currentSchool, $this->getAuthenticatedUser());
        return ApiResponseService::success("School Events Fetched Successfully", $schoolEvents, null, 200);
    }
    public function getScheduledSchoolEventsByCategory(Request $request, $eventCategoryId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $scheduledSchoolEvents = $this->eventService->getScheduledSchoolEventsByCategory($currentSchool,  $eventCategoryId);
        return ApiResponseService::success("Scheduled School Events Fetched Successfully", $scheduledSchoolEvents, null, 2000);
    }
    public function getScheduledSchoolEvents(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $scheduledSchoolEvents = $this->eventService->getScheduledSchoolEvents($currentSchool);
        return ApiResponseService::success("Scheduled School Events Fetched Successfully", $scheduledSchoolEvents, null, 200);
    }
    public function getDraftSchoolEvents(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $draftSchoolEvents = $this->eventService->getDraftSchoolEvents($currentSchool);
        return ApiResponseService::success("Draft School Events Fetched Successfully", $draftSchoolEvents, null, 200);
    }
    public function getDraftSchoolEventsByCategory(Request $request, $eventCategoryId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $draftSchoolEvents = $this->eventService->getDraftSchoolEventsByCategory($currentSchool,  $eventCategoryId);
        return ApiResponseService::success("Draft School Events Fetched Successfully", $draftSchoolEvents, null, 200);
    }
    public function getStudentUpcomingSchoolEvents(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authStudent = $this->getAuthenticatedUser();
        $schoolEvents = $this->eventService->getStudentUpcomingEvents($currentSchool, $authStudent['authUser']);
        return ApiResponseService::success("Upcoming School Events Fetched Successfully", $schoolEvents, null, 200);
    }
    private function getAuthenticatedUser()
    {
        $user = Auth::user();

        if ($user instanceof Model) {
            return [
                'userId' => $user->id,
                'userType' => get_class($user),
                'authUser' => $user
            ];
        }

        return [
            'userId' => null,
            'userType' => null,
            'authUser' => $user
        ];
    }
}
