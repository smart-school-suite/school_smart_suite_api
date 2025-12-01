<?php

namespace App\Http\Controllers\Announcement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Announcement\CreateAnnouncementRequest;
use App\Http\Requests\Announcement\UpdateAnnouncementContentRequest;
use App\Http\Requests\Announcement\UpdateDraftAnnouncement;
use App\Http\Resources\AnnouncementResource;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\Announcement\AnnouncementService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Services\Announcement\CreateAnnouncementService;
use App\Services\Announcement\UpdateDraftAnnouncementService;
class AnnouncementController extends Controller
{
       protected CreateAnnouncementService $createAnnouncementService;
    protected AnnouncementService $announcementService;
    protected UpdateDraftAnnouncementService $updateDraftAnnouncementService;
    public function __construct(
        CreateAnnouncementService $createAnnouncementService,
        AnnouncementService $announcementService,
        UpdateDraftAnnouncementService $updateDraftAnnouncementService
    ) {
        $this->createAnnouncementService = $createAnnouncementService;
        $this->announcementService = $announcementService;
        $this->updateDraftAnnouncementService = $updateDraftAnnouncementService;
    }

    public function updateAnnouncementDraft(UpdateDraftAnnouncement $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authenticatedUser = $this->getAuthenticatedUser();
        $this->updateDraftAnnouncementService->updateDraftAnnouncement($currentSchool, $authenticatedUser, $request->validated());
        return ApiResponseService::success("Announcement Draft Updated Successfully", null, null, 200);
    }
    public function getAnnouncementEngagementOverview(Request $request, $announcementId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $engagementStats = $this->announcementService->getAnnouncementEngagementOverview($currentSchool, $announcementId);
        return ApiResponseService::success("Annoucement Engagement Stats Fetched Successfully", $engagementStats, null, 200);
    }
    public function getAnnouncementReadUnreadList(Request $request, $announcementId)
    {
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
        $authenticatedUser = $this->getAuthenticatedUser();
        $updateAnnouncementContent = $this->announcementService->updateAnnouncementContent($request->validated(), $currentSchool, $announcementId, $authenticatedUser['authUser']);
        return ApiResponseService::success("Announcement Content Updated Successfully", $updateAnnouncementContent, null, 200);
    }
    public function deleteAnnouncement(Request $request, $announcementId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authenticatedUser = $this->getAuthenticatedUser();
        $deleteAnnouncement = $this->announcementService->deleteAnnouncement($announcementId, $currentSchool, $authenticatedUser['authUser']);
        return ApiResponseService::success("Announcement Deleted Successfully", $deleteAnnouncement, null, 200);
    }
    public function getAnnouncementByState(Request $request, $status)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getAnnouncement = $this->announcementService->getAnnoucementsByState($currentSchool, $status);
        return ApiResponseService::success("Announcement Fetched Successfully", AnnouncementResource::collection($getAnnouncement), null, 200);
    }

    public function getAllStudentAnnouncement(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authenticatedUser = $this->getAuthenticatedUser();
        $announcements = $this->announcementService->getAllStudentAnnouncements($currentSchool, $authenticatedUser['authUser']);
        return ApiResponseService::success("Student Announcements Fetched Successfully", $announcements, null, 200);
    }

    public function getAllStudentAnnouncementLabelId(Request $request, $labelId){
        $currentSchool = $request->attributes->get('currentSchool');
        $authenticatedUser = $this->getAuthenticatedUser();
        $announcements = $this->announcementService->getStudentAnnouncementLabelId($currentSchool, $authenticatedUser['authUser'], $labelId);
        return ApiResponseService::success("Student Announcements Fetched Successfully", $announcements, null, 200);
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
