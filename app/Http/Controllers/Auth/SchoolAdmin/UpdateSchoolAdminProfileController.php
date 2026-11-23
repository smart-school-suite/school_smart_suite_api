<?php

namespace App\Http\Controllers\Auth\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolAdmin\UpdateSchoolAdminProfileRequest;
use App\Services\ApiResponseService;
use App\Services\Auth\SchoolAdmin\UpdateSchoolAdminProfileService;
use App\Support\SchoolAdmin\UsernameRules;
use Illuminate\Http\Request;

class UpdateSchoolAdminProfileController extends Controller
{
    protected UpdateSchoolAdminProfileService $updateSchoolAdminProfileService;

    public function __construct(UpdateSchoolAdminProfileService $updateSchoolAdminProfileService)
    {
        $this->updateSchoolAdminProfileService = $updateSchoolAdminProfileService;
    }

    public function UpdateSchoolAdminProfile(UpdateSchoolAdminProfileRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $this->updateSchoolAdminProfileService->updateSchoolAdminProfile($request->validated(), $currentSchool);
        return ApiResponseService::success("School Admin Profile Updated Successfully", null, null, 200);
    }

    public function checkUsernameAvailability(Request $request)
    {
        $request->validate(['username' => 'required|string|max:100']);

        $result = UsernameRules::checks(
            $request->query('username'),
            auth()->guard('schooladmin')->id()
        );

        return ApiResponseService::success("Username Checked Successfully", $result, null, 200);
    }
}
