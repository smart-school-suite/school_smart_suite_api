<?php

namespace App\Http\Controllers\SchoolEvent;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventCategory\CreateEventCategoryRequest;
use App\Http\Requests\EventCategory\UpdateEventCategoryRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\SchoolEvent\EventCategoryService;
use Svg\Tag\Rect;

class SchoolEventCategoryController extends Controller
{
    protected EventCategoryService $eventCategoryService;

    public function __construct(EventCategoryService $eventCategoryService)
    {
        $this->eventCategoryService = $eventCategoryService;
    }

    public function createSchoolEventCategory(CreateEventCategoryRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $createSchoolEvent = $this->eventCategoryService->createEventCategory($currentSchool, $request->validated());
        return ApiResponseService::success("School Event Category Deleted Successfully", $createSchoolEvent, null, 201);
    }

    public function updateSchoolEventCagory(UpdateEventCategoryRequest $request, $eventCategoryId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $this->eventCategoryService->updateEventCategory($currentSchool, $request->validated(), $eventCategoryId);
        return ApiResponseService::success("School Event Category Updated Successfully", null, null, 200);
    }

    public function deleteSchoolEventCategory(Request $request, $eventCategoryId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $this->eventCategoryService->deleteEventCategory($currentSchool, $eventCategoryId);
        return ApiResponseService::success("School Event Category Deleted Successfully", null, null, 200);
    }

    public function getSchoolEventCategory(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $schoolEventCategories = $this->eventCategoryService->getEventCategory($currentSchool);
        return ApiResponseService::success("School Event Categories Fetched Successfully", $schoolEventCategories, null, 200);
    }

    public function activateSchoolEventCategory(Request $request, $eventCategoryId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $this->eventCategoryService->activateEventCategory($currentSchool, $eventCategoryId);
        return ApiResponseService::success("School Event Category Activated Successfully", null, null, 200);
    }

    public function deactivateSchoolEventCategory(Request $request, $eventCategoryId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $this->eventCategoryService->deactivateEventCategory($currentSchool, $eventCategoryId);
        return ApiResponseService::success("School Event Category Deactivated Successfully", null, null, 200);
    }

    public function getActiveSchoolEventCategory(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $activeSchoolEventCategory = $this->eventCategoryService->getActiveEventCategory($currentSchool);
        return ApiResponseService::success("Active School Event Category Fetched Successfully", $activeSchoolEventCategory, null,  200);
    }

    public function getSchoolEventCategoryDetails(Request $request, $eventCategoryId){
         $currentSchool = $request->attributes->get('currentSchool');
         $eventCategoryDetails = $this->eventCategoryService->getEventCategoryDetails($currentSchool, $eventCategoryId);
         return ApiResponseService::success("Event Category Details Fetched Successfully", $eventCategoryDetails, null, 200);
    }
}
