<?php

namespace App\Http\Controllers;

use App\Models\Schooladmin;
use App\Services\ApiResponseService;
use Illuminate\Support\Facades\Hash;
use App\Services\SchoolAdminService;
use App\Http\Requests\CreateSchoolAdminSignUpRequest;
use Illuminate\Http\Request;


class SchoolAdminController extends Controller
{
    //
    protected SchoolAdminService $schoolAdminService;
    public function __construct(SchoolAdminService $schoolAdminService)
    {
        $this->schoolAdminService = $schoolAdminService;
    }
    public function updateSchoolAdmin(Request $request,)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $school_admin_id = $request->route("school_admin_id");
        $updateSchoolAdmin = $this->schoolAdminService->updateSchoolAdmin($request->validated, $school_admin_id, $currentSchool);
        return ApiResponseService::success("Admin Updated Sucessfully", $updateSchoolAdmin, null, 200);
    }

    public function deleteSchoolAdmin(Request $request, $school_admin_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteSchoolAdmin = $this->schoolAdminService->deleteSchoolAdmin($school_admin_id, $currentSchool);
        return ApiResponseService::success('School Admin Deleled Sucessfully', $deleteSchoolAdmin, null, 200);
    }

    public function getSchoolAdmin(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $schoolAdmins = $this->schoolAdminService->getSchoolAdmins($currentSchool);
        return ApiResponseService::success("School Admin Fetched Successfully", $schoolAdmins, null, 200);
    }

    public function getSchoolAdminDetails(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $school_admin_id = $request->route('school_admin_id');
        $schoolAdminDetails = $this->schoolAdminService->getSchoolAdminDetails($currentSchool, $school_admin_id);
        return ApiResponseService::success("School Admin Details Fetched Successfully", $schoolAdminDetails, null, 200);
    }

    public function createAdminOnSignup(CreateSchoolAdminSignUpRequest $request)
    {

        $createSchoolAdmin = $this->schoolAdminService->createSchoolAdmin($request->validated());
        return ApiResponseService::success("School Admin Created Sucessfully", $createSchoolAdmin, null, 201);
    }
}
