<?php

namespace App\Http\Controllers\SchoolEvent;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventTag\CreateEventTagRequest;
use App\Http\Requests\EventTag\UpdateEventTagRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\SchoolEvent\EventTagService;


class SchoolEventTagController extends Controller
{
       protected EventTagService $eventTagService;
    public function __construct(EventTagService $eventTagService)
    {
        $this->eventTagService = $eventTagService;
    }
    public function createEventTag(CreateEventTagRequest $request)
    {
        $createTag = $this->eventTagService->createEventTag($request->validated());
        return ApiResponseService::success("Event Tag Created Successfully", $createTag, null, 201);
    }
    public function updateEventTag(UpdateEventTagRequest $request, $tagId)
    {
        $updateTag = $this->eventTagService->updateTag($request->validated(), $tagId);
        return ApiResponseService::success("Event Tag Updated Successfully", $updateTag, null, 200);
    }
    public function deleteEventTag(Request $request, $tagId)
    {
        $deleteTag = $this->eventTagService->deleteTag($tagId);
        return ApiResponseService::success("Event Tag Deleted Successfully", $deleteTag, null, 200);
    }
    public function getEventTag(Request $request)
    {
        $geEventTags = $this->eventTagService->getTags();
        return ApiResponseService::success("Event Tag Fetched Successfully", $geEventTags, null, 200);
    }
}
