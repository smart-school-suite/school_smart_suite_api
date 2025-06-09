<?php

namespace App\Http\Controllers;

use App\Http\Requests\Event\UpdateEventSettingRequest;
use Illuminate\Http\Request;
use App\Services\SchoolEventSettingService;
use App\Services\ApiResponseService;
use Throwable;
class SchoolEventSettingController extends Controller
{
    protected SchoolEventSettingService $schoolEventSettingService;

    public function __construct(SchoolEventSettingService $schoolEventSettingService){
        $this->schoolEventSettingService = $schoolEventSettingService;
    }

    public function getSettings(Request $request){
        try{
            $currentSchool = $request->attributes->get('currentSchool');
            $getSchoolEventSetting = $this->schoolEventSettingService->getSettings($currentSchool);
            return ApiResponseService::success("School Event Settings Fetched Successfully", $getSchoolEventSetting, null, 200);
        }
        catch(Throwable $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    public function updateSetting(UpdateEventSettingRequest $request, $settingId) {
       try{
            $currentSchool = $request->attributes->get('currentSchool');
            $updateSettings = $this->schoolEventSettingService->updateSetting($currentSchool, $request->validated(), $settingId);
            return ApiResponseService::success("School Announcement Setting Updated Successfully", $updateSettings, null, 200);
        }
        catch(Throwable $e){
             return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }
}
