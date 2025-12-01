<?php

namespace App\Http\Controllers\Semester;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\SchoolSemester\CreateSchoolSemesterRequest;
use App\Http\Requests\SchoolSemester\SchoolSemesterIdRequest;
use App\Http\Requests\SchoolSemester\UpdateSchoolSemesterRequest;
use App\Http\Requests\SchoolSemester\BulkUpdateSchoolSemesterRequest;
use App\Services\ApiResponseService;
use App\Services\Semester\SchoolSemesterService;
use Exception;

class SchoolSemesterController extends Controller
{
    protected SchoolSemesterService $schoolSemesterService;
    public function __construct(SchoolSemesterService $schoolSemesterService)
    {
        $this->schoolSemesterService = $schoolSemesterService;
    }
    public function createSchoolSemester(CreateSchoolSemesterRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $createSchoolSemester = $this->schoolSemesterService->createSchoolSemester($request->validated(), $currentSchool, $authAdmin);
        return ApiResponseService::success("School Semester Created Succesfully", $createSchoolSemester, null, 201);
    }
    public function deleteSchoolSemester(Request $request, $schoolSemesterId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $deleteSchoolSemester = $this->schoolSemesterService->deleteSchoolSemester($schoolSemesterId, $currentSchool, $authAdmin);
        return ApiResponseService::success("School Deleted Sucessfully", $deleteSchoolSemester, null, 200);
    }
    public function updateSchoolSemester(UpdateSchoolSemesterRequest $request, $schoolSemesterId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $updateSchoolSemester = $this->schoolSemesterService->updateSchoolSemester($request->validated(), $currentSchool, $schoolSemesterId, $authAdmin);
        return ApiResponseService::success("School Semester Updated Successfully", $updateSchoolSemester, null, 200);
    }
    public function getSchoolSemester(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $getSchoolSemesters = $this->schoolSemesterService->getSchoolSemesters($currentSchool);
        return ApiResponseService::success("School Semester Fetched Sucessfully", $getSchoolSemesters, null, 200);
    }
    public function getSchoolSemesterDetails(Request $request, $schoolSemesterId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $getSchoolSemesterDetails = $this->schoolSemesterService->getSchoolSemesterDetail($currentSchool, $schoolSemesterId);
        return ApiResponseService::success("School Semester Details Fetched Successfully", $getSchoolSemesterDetails, null, 200);
    }
    public function bulkUpdateSchoolSemester(BulkUpdateSchoolSemesterRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $bulkUpdateSchoolSemester = $this->schoolSemesterService->bulkUpdateSchoolSemester($request->school_semester, $currentSchool, $authAdmin);
        return ApiResponseService::success("School Semester Updated Successfully", $bulkUpdateSchoolSemester, null, 200);
    }
    public function bulkDeleteSchoolSemester(SchoolSemesterIdRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $bulkDeleteSchoolSemester = $this->schoolSemesterService->bulkDeleteSchoolSemester($request->schoolSemesterIds, $currentSchool, $authAdmin);
        return ApiResponseService::success("School Semester Deleted Successfully", $bulkDeleteSchoolSemester, null, 200);
    }
    public function getActiveSchoolSemesters(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $activeSchoolSemesters = $this->schoolSemesterService->getActiveSchoolSemesters($currentSchool);
        return ApiResponseService::success("Active School Semesters Fetched Successfully", $activeSchoolSemesters, null, 200);
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
