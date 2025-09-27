<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnnouncementTag\CreateAnnouncementTagRequest;
use App\Http\Requests\AnnouncementTag\UpdateAnnouncementTagRequest;
use Illuminate\Http\Request;
use App\Services\AnnouncementTagService;
use App\Services\ApiResponseService;
use Throwable;

class AnnouncementTagController extends Controller
{
   protected AnnouncementTagService $announcementTagService;
   public function __construct(AnnouncementTagService $announcementTagService){
      $this->announcementTagService = $announcementTagService;
   }

   public function createTag(CreateAnnouncementTagRequest $request){
      try{
          $createTag = $this->announcementTagService->createTag($request->validated());
          return ApiResponseService::success("Announcement Tag Created Successfully", $createTag, null, 201);
      }
      catch(Throwable $e){
         return ApiResponseService::error($e->getMessage(), null, 500);
      }
   }

   public function updateTag(UpdateAnnouncementTagRequest $request, $tagId){
      try{
         $currentSchool = $request->attributes->get('currentSchool');
         $updateTag = $this->announcementTagService->updateTag($request->validated(), $currentSchool, $tagId);
         return ApiResponseService::success("Announcement Tag Updated Successfully", $updateTag, null, 200);
      }
      catch(Throwable $e){
        return ApiResponseService::error($e->getMessage(), null, 500);
      }
   }

   public function deleteTag(Request $request, $tagId){
      try{
           $deleteTag = $this->announcementTagService->deleteTag($tagId);
           return ApiResponseService::success("Announcement Tag Deleted Successfully", $deleteTag, null, 200);
      }
      catch(Throwable $e){
        return ApiResponseService::error($e->getMessage(), null, 500);
      }
   }

   public function getAnnouncementTags(Request $request){
      try{
         $getTags = $this->announcementTagService->getTags();
         return ApiResponseService::success("Get Annoncement Tags", $getTags, null, 200);
      }
      catch(Throwable $e){
        return ApiResponseService::error($e->getMessage(), null, 500);
      }
   }
}
