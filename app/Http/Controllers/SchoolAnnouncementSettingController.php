<?php

namespace App\Http\Controllers;

use App\Http\Requests\SchoolAnnouncementSetting\UpdateSchoolAnnouncementSettingRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\SchoolAnnouncementSettingService;
use Throwable;

class SchoolAnnouncementSettingController extends Controller
{
    protected SchoolAnnouncementSettingService $schoolAnnouncementSettingService;
    public function __construct(SchoolAnnouncementSettingService $schoolAnnouncementSettingService){
        $this->schoolAnnouncementSettingService = $schoolAnnouncementSettingService;
    }

    public function getSchoolAnnouncementSettings(Request $request){
        try{
            $currentSchool = $request->attributes->get('currentSchool');
            $getAnnouncementSettings = $this->schoolAnnouncementSettingService->getSettings($currentSchool);
            return ApiResponseService::success("School Announcement Settings Fetched Successfully", $getAnnouncementSettings, null, 200);
        }
        catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    public function updateSchoolAnnouncement(UpdateSchoolAnnouncementSettingRequest $request, $settingId){
        try{
            $currentSchool = $request->attributes->get('currentSchool');
            $updateSettings = $this->schoolAnnouncementSettingService->updateSetting($currentSchool, $request->validated(), $settingId);
            return ApiResponseService::success("School Announcement Setting Updated Successfully", $updateSettings, null, 200);
        }
        catch(Throwable $e){
             return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }
}
