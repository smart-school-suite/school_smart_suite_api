<?php

namespace App\Http\Controllers\Auth\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolAdmin\UpdateSchoolAdminRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\Auth\SchoolAdmin\UpdateSchoolAdminProfileService;
use Exception;

class UpdateSchoolAdminProfileController extends Controller
{
    protected UpdateSchoolAdminProfileService $updateSchoolAdminProfileService;
    public function __construct(UpdateSchoolAdminProfileService $updateSchoolAdminProfileService){
        $this->updateSchoolAdminProfileService = $updateSchoolAdminProfileService;
    }
    public function UpdateSchoolAdminProfile(UpdateSchoolAdminRequest $request){
         $currentSchool = $request->attributes->get('currentSchool');
            $this->updateSchoolAdminProfileService->updateSchoolAdminProfile($request->validated(), $currentSchool);
            return ApiResponseService::success("School Admin Profile Updated Successfully", null, null, 200);
    }
}
