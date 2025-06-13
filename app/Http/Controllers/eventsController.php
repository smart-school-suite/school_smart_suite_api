<?php

namespace App\Http\Controllers;

use App\Http\Requests\Event\UpdateEventContentRequest;
use App\Services\ApiResponseService;
use App\Http\Requests\Event\CreateEventRequest;
use App\Http\Requests\Event\UpdateEventDraftRequest;
use App\Services\UpdateEventDraftStatusService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Services\CreateEventService;
use App\Services\SchoolEventService;
use Throwable;

class EventsController extends Controller
{
    protected CreateEventService $createEventService;
    protected SchoolEventService $schoolEventService;
    protected UpdateEventDraftStatusService $updateEventDraftStatusService;
    public function __construct(
        CreateEventService $createEventService,
         SchoolEventService $schoolEventService,
         UpdateEventDraftStatusService $updateEventDraftStatusService

        )
    {
        $this->createEventService = $createEventService;
        $this->schoolEventService = $schoolEventService;
        $this->updateEventDraftStatusService = $updateEventDraftStatusService;
    }

    public function createSchoolEvent(CreateEventRequest $request)
    {
        try {
            $currentSchool = $request->attributes->get('currentSchool');
            $authenticatedUser = $this->getAuthenticatedUser();
            $eventBackgroundImg = $request->file('background_image');
            $createSchoolEvent = $this->createEventService->createEvent($request->validated(), $currentSchool, $authenticatedUser, $eventBackgroundImg);
            return ApiResponseService::success("School Event Created Successfully", $createSchoolEvent, null, 201);
        } catch (Throwable $e) {
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    private function getAuthenticatedUser()
    {
        $user = Auth::user();

        if ($user instanceof Model) {
            return [
                'userId' => $user->id,
                'userType' => get_class($user),
            ];
        }

        return [
            'userId' => null,
            'userType' => null,
        ];
    }

    public function getSchoolEvents(Request $request){
       try{
        $currentSchool = $request->attributes->get('currentSchool');
         $schoolEvents = $this->schoolEventService->getSchoolEvents($currentSchool);
         return ApiResponseService::success("School Events Fetched Successfully", $schoolEvents, null, 200);
       }
       catch(Throwable $e){
        return ApiResponseService::error($e->getMessage(), null, 500);
       }
    }

    public function updateSchoolEventContent(UpdateEventContentRequest $request, $eventId){
         try{
            $currentSchool = $request->attributes->get('currentSchool');
            $eventBackgroundImg = $request->file('background_image');
            $updateContent = $this->schoolEventService->updateEventContent($request->validated(), $eventId, $currentSchool, $eventBackgroundImg);
            return ApiResponseService::success("School Event Content Updated Successfully", $updateContent, null);
         }
         catch(Throwable $e){
        return ApiResponseService::error($e->getMessage(), null, 500);
       }
    }

    public function getSchoolEventByCategory(Request $request, $categoryId){
        try{
            $currentSchool = $request->attributes->get('currentSchool');
            $schoolEvents = $this->schoolEventService->getEventByCategory($currentSchool, $categoryId);
            return ApiResponseService::success("School Event FetchedS Successfully", $schoolEvents, null, 200);
        }
        catch(Throwable $e){
        return ApiResponseService::error($e->getMessage(), null, 500);
       }
    }

    public function deleteSchoolEvent(Request $request, $eventId){
        try{
            $currentSchool = $request->attributes->get('currentSchool');
            $deleteEvent = $this->schoolEventService->deleteEvent($currentSchool, $eventId);
            return ApiResponseService::success("School Event Deleted Successfully", $deleteEvent, null, 200);
        }
        catch(Throwable $e){
        return ApiResponseService::error($e->getMessage(), null, 500);
       }
    }

    public function getSchoolEventDetails(Request $request, $eventId){
        try{
           $currentSchool = $request->attributes->get('currentSchool');
           $eventDetails = $this->schoolEventService->getEventDetails($currentSchool, $eventId);
           return ApiResponseService::success("School Event Details Fetched Successfully", $eventDetails, null, 200);
        }
         catch(Throwable $e){
        return ApiResponseService::error($e->getMessage(), null, 500);
       }
    }

    public function getSchoolEventByStatus(Request $request, $status){
         try{
            $currentSchool = $request->attributes->get('currentSchool');
            $schoolEvents = $this->schoolEventService->getEventsByStatus($currentSchool, $status);
            return ApiResponseService::success("School Events Fetched Successfully", $schoolEvents, null, 200);
         }
         catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
         }
    }

    public function updateSchoolEventStatus(UpdateEventDraftRequest $request, $eventId){
        try{
            $updateSchoolEvent = $this->updateEventDraftStatusService->updateEventDraftStatus($request->validated(), $eventId);
            return ApiResponseService::success("Event Updated Successfully", $updateSchoolEvent, null, 200);
        }
        catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }
}
