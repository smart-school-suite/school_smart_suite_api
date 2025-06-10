<?php

namespace App\Http\Controllers;

use App\Http\Requests\Event\UpdateEventRequest;
use App\Http\Requests\EventTag\CreateEventTagRequest;
use App\Http\Requests\EventTag\UpdateEventTagRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\EventTagService;
use Throwable;

class EventTagController extends Controller
{
    protected EventTagService $eventTagService;
    public function __construct(EventTagService $eventTagService){
        $this->eventTagService = $eventTagService;
    }

    public function createEventTag(CreateEventTagRequest $request){
        try{
           $currentSchool = $request->attributes->get('currentSchool');
           $createTag = $this->eventTagService->createEventTag($request->validated(), $currentSchool);
           return ApiResponseService::success("Event Tag Created Successfully", $createTag, null, 201);
        }
        catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    public function updateEventTag(UpdateEventTagRequest $request, $tagId) {
        try{
           $currentSchool = $request->attributes->get('currentSchool');
           $updateTag = $this->eventTagService->updateTag($request->validated(), $currentSchool, $tagId);
           return ApiResponseService::success("Event Tag Updated Successfully", $updateTag, null, 200);
        }
        catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    public function deleteEventTag(Request $request, $tagId) {
       try{
           $currentSchool = $request->attributes->get('currentSchool');
           $deleteTag = $this->eventTagService->deleteTag($currentSchool, $tagId);
           return ApiResponseService::success("Event Tag Deleted Successfully", $deleteTag, null, 200);
       }
       catch(Throwable $e){
         return ApiResponseService::error($e->getMessage(), null, 500);
       }
    }

    public function getEventTag(Request $request){
         try{
           $currentSchool = $request->attributes->get('currentSchool');
           $geEventTags = $this->eventTagService->getTags($currentSchool);
           return ApiResponseService::success("Event Tag Fetched Successfully", $geEventTags, null, 200);
         }
         catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
         }
    }
}
