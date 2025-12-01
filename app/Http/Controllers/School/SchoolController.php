<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ApiResponseService;
use App\Http\Requests\School\UpdateSchoolRequest;
use App\Http\Requests\School\UploadSchoolLogoRequest;
use App\Services\School\SchoolService;

class SchoolController extends Controller
{
    protected SchoolService $schoolService;
    public function __construct(SchoolService $schoolService)
    {
        $this->schoolService = $schoolService;
    }

    public function updateSchool(UpdateSchoolRequest $request, $schoolId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $updateSchool = $this->schoolService->updateSchool($request->validated(), $schoolId, $currentSchool, $authAdmin);
        return ApiResponseService::success("School Updated Sucessfully", $updateSchool, null, 200);
    }
    public function deleteSchool(Request $request, $schoolId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteSchool = $this->schoolService->deleteSchool($schoolId, $currentSchool, $authAdmin);
        return ApiResponseService::success("School Deleted Succefully", $deleteSchool, null, 200);
    }
    public function getSchoolDetails(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $getSchoolDetails = $this->schoolService->getSchoolDetails($currentSchool->school_id);
        return ApiResponseService::success("School Details Fetched Sucessfully", $getSchoolDetails, null, 200);
    }
    public function uploadSchoolLogo(UploadSchoolLogoRequest $request, $schoolId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $this->schoolService->uploadSchoolLogo($request, $schoolId, $currentSchool, $authAdmin);
        return ApiResponseService::success("School Logo Updated Successfully", null, null, 200);
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
