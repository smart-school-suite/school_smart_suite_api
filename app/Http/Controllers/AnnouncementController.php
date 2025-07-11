<?php

namespace App\Http\Controllers;

use App\Http\Requests\Announcement\CreateAnnouncementRequest;
use App\Http\Requests\Announcement\UpdateAnnouncementContentRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\AnnouncementService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Services\CreateAnnouncementService;
use Throwable;

class AnnouncementController extends Controller
{
    protected CreateAnnouncementService $createAnnouncementService;
    protected AnnouncementService $announcementService;
    public function __construct(CreateAnnouncementService $createAnnouncementService, AnnouncementService $announcementService){
        $this->createAnnouncementService = $createAnnouncementService;
        $this->announcementService = $announcementService;
    }

    public function createAnnoucement(CreateAnnouncementRequest $request){
        try{
            $currentSchool = $request->attributes->get('currentSchool');
            $authenticatedUser = $this->getAuthenticatedUser();
          $createAnnouncement =   $this->createAnnouncementService->createAnnouncement($currentSchool, $request->validated(), $authenticatedUser);
            return ApiResponseService::success("Announcement Created Successfully", $createAnnouncement, null, 200);
        }
        catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    public function getAnnouncementDetails(Request $request, $announcementId){
        $currentSchool = $request->attributes->get('currentSchool');
       $announcementDetails = $this->announcementService->getAnnouncementDetails($currentSchool, $announcementId);
       return ApiResponseService::success("Announcement Details Fetched Successfully", $announcementDetails, null, 200);
    }
    public function updateAnnouncementContent(UpdateAnnouncementContentRequest $request, string $announcementId){
         try{
            $currentSchool = $request->attributes->get('currentSchool');
            $updateAnnouncementContent = $this->announcementService->updateAnnouncementContent($request->validated(), $currentSchool, $announcementId);
            return ApiResponseService::success("Announcement Content Updated Successfully", $updateAnnouncementContent, null, 200);
         }
         catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
         }
    }

    public function deleteAnnouncement(Request $request, $announcementId){
        try{
             $currentSchool = $request->attributes->get('currentSchool');
             $deleteAnnouncement = $this->announcementService->deleteAnnouncement($announcementId, $currentSchool);
             return ApiResponseService::success("Announcement Deleted Successfully", $deleteAnnouncement, null, 200);
        }
        catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    public function getAnnouncementByState(Request $request, $status){
        try{
           $currentSchool = $request->attributes->get('currentSchool');
           $getAnnouncement = $this->announcementService->getAnnoucementsByState($currentSchool, $status);
           return ApiResponseService::success("Announcement Fetched Successfully", $getAnnouncement, null, 200);
        }
        catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    private function getAuthenticatedUser(){
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


}
