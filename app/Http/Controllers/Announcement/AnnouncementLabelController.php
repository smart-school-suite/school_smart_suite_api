<?php

namespace App\Http\Controllers\Announcement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\AnnouncementLabel\CreateAnnouncementLabelRequest;
use App\Http\Requests\AnnouncementLabel\UpdateAnnouncementLabelRequest;
use App\Services\ApiResponseService;
use App\Services\Announcement\AnnouncementLabelService;
use Throwable;
class AnnouncementLabelController extends Controller
{
        protected AnnouncementLabelService $announcementLabelService;

    public function __construct(AnnouncementLabelService $announcementLabelService){
        $this->announcementLabelService = $announcementLabelService;
    }

    public function createAnnouncementLabel(CreateAnnouncementLabelRequest $request){
         try{
            $createAnnouncementLabel = $this->announcementLabelService->createLabel($request->validated());
            return ApiResponseService::success("Announcement Label Created Successfully", $createAnnouncementLabel, null, 201);
         }
         catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
         }
    }

    public function updateAnnouncementLabel(UpdateAnnouncementLabelRequest $request, $labelId){
        try{
            $updateAnnouncementLabel = $this->announcementLabelService->updateLabel($request->validated(), $labelId);
            return ApiResponseService::success("Announcement Label Updated Successfully", $updateAnnouncementLabel, null, 200);
        }
        catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    public function deleteAnnouncementLabel($labelId){
        try{
           $deleteAnnouncement = $this->announcementLabelService->deleteLabel($labelId);
           return ApiResponseService::success("Announcement Label Deleted Successfully", $deleteAnnouncement, null, 200);
        }
        catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    public function getAnnouncementLabels(){
        try{
            $getAnnouncementLabels = $this->announcementLabelService->getLabels();
            return ApiResponseService::success("Announcement Labels Fetched Successfully", $getAnnouncementLabels, null, 200);
        }
        catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }
}
