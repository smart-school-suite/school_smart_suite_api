<?php

namespace App\Http\Controllers;

use App\Http\Requests\Announcement\CreateAnnouncementRequest;
use App\Http\Requests\Announcement\UpdateAnnouncementContentRequest;
use App\Http\Requests\Announcement\UpdateDraftAnnouncement;
use App\Http\Resources\AnnouncementResource;
use App\Services\ApiResponseService;
use App\Services\UpdateAnnouncementDraftService;
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
    protected UpdateAnnouncementDraftService $updateAnnouncementDraftService;
    public function __construct(
    CreateAnnouncementService $createAnnouncementService,
    AnnouncementService $announcementService,
    UpdateAnnouncementDraftService $updateAnnouncementDraftService
    )
    {
        $this->createAnnouncementService = $createAnnouncementService;
        $this->announcementService = $announcementService;
        $this->updateAnnouncementDraftService = $updateAnnouncementDraftService;
    }

    public function updateAnnouncementDraft(UpdateDraftAnnouncement $request){
       $currentSchool = $request->attributes->get('currentSchool');
       $authenticatedUser = $this->getAuthenticatedUser();
       $this->updateAnnouncementDraftService->updateDraftAnnouncement($currentSchool, $authenticatedUser,$request->validated());
       return ApiResponseService::success("Announcement Draft Updated Successfully", null, null, 200);
    }
    public function getAnnouncementEngagementOverview(Request $request, $announcementId){
         $currentSchool = $request->attributes->get('currentSchool');
        $engagementStats = $this->announcementService->getAnnouncementEngagementOverview($currentSchool, $announcementId);
        return ApiResponseService::success("Annoucement Engagement Stats Fetched Successfully", $engagementStats, null, 200);
    }
    public function getAnnouncementReadUnreadList(Request $request, $announcementId){
        $currentSchool = $request->attributes->get('currentSchool');
        $list = $this->announcementService->getAnnouncementReadUnreadList($currentSchool, $announcementId);
        return ApiResponseService::success("Announcement List Fetched Successfully", $list, null, 200);
    }
    public function createAnnoucement(CreateAnnouncementRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authenticatedUser = $this->getAuthenticatedUser();
        $this->createAnnouncementService->createAnnouncement($currentSchool,  $authenticatedUser, $request->validated());
        return ApiResponseService::success("Announcement Created Successfully", null, null, 200);
    }
    public function getAnnouncementDetails(Request $request, $announcementId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $announcementDetails = $this->announcementService->getAnnouncementDetails($currentSchool, $announcementId);
        return ApiResponseService::success("Announcement Details Fetched Successfully", $announcementDetails, null, 200);
    }
    public function updateAnnouncementContent(UpdateAnnouncementContentRequest $request, string $announcementId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateAnnouncementContent = $this->announcementService->updateAnnouncementContent($request->validated(), $currentSchool, $announcementId);
        return ApiResponseService::success("Announcement Content Updated Successfully", $updateAnnouncementContent, null, 200);
    }
    public function deleteAnnouncement(Request $request, $announcementId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteAnnouncement = $this->announcementService->deleteAnnouncement($announcementId, $currentSchool);
        return ApiResponseService::success("Announcement Deleted Successfully", $deleteAnnouncement, null, 200);
    }
    public function getAnnouncementByState(Request $request, $status)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getAnnouncement = $this->announcementService->getAnnoucementsByState($currentSchool, $status);
        return ApiResponseService::success("Announcement Fetched Successfully", AnnouncementResource::collection($getAnnouncement), null, 200);
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
