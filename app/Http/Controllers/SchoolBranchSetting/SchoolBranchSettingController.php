<?php

namespace App\Http\Controllers\SchoolBranchSetting;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolBranchSetting\UpdateSchoolBranchSettingRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\SchoolBranchSetting\SchoolBranchSettingService;

class SchoolBranchSettingController extends Controller
{
    protected SchoolBranchSettingService $schoolBranchSettingService;
    public function __construct(
        SchoolBranchSettingService $schoolBranchSettingService,
    ) {
        $this->schoolBranchSettingService = $schoolBranchSettingService;
    }

    public function updateSetting(UpdateSchoolBranchSettingRequest $request)
    {
        $admin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $updateSetting = $this->schoolBranchSettingService->update($currentSchool, $request->validated(), $admin);
        return ApiResponseService::success("School Branch Setting Updated Successfully", $updateSetting, null, 200);
    }
    public function getSchoolBranchSetting(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $schoolBranchSetting = $this->schoolBranchSettingService->getSchoolBranchSetting($currentSchool);
        return ApiResponseService::success("School Branch Setting Fetched Successfully", $schoolBranchSetting, null, 200);
    }
    public function getSchoolBranchSettingDetails(Request $request, string $settingId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $schoolBranchSettingDetails = $this->schoolBranchSettingService->getSchoolBranchSettingDetails($currentSchool, $settingId);
        return ApiResponseService::success("School Branch Setting Details Fetched Successfully", $schoolBranchSettingDetails, null, 200);
    }
    protected function resolveUser()
    {
        foreach (['student', 'teacher', 'schooladmin'] as $guard) {
            $user = request()->user($guard);
            if ($user !== null) {
                return $user;
            }
        }
        return null;
    }
}
