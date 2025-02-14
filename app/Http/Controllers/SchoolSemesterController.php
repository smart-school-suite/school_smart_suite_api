<?php

namespace App\Http\Controllers;
use App\Http\Requests\SchoolSemesterRequest;
use App\Http\Requests\UpdateSchoolSemesterRequest;
use App\Services\ApiResponseService;
use App\Services\SchoolSemesterService;
use Illuminate\Http\Request;

class SchoolSemesterController extends Controller
{
    //
    protected SchoolSemesterService $schoolSemesterService;
    public function __construct(SchoolSemesterService $schoolSemesterService){
        $this->schoolSemesterService = $schoolSemesterService;
    }

    public function createSchoolSemester(SchoolSemesterRequest $request){
        $currentSchool = $request->attributes->get("currentSchool");
        $createSchoolSemester = $this->schoolSemesterService->createSchoolSemester($request->validated(), $currentSchool);
        return ApiResponseService::success("School Semester Created Succesfully", $createSchoolSemester, null, 201);
    }

    public function deleteSchoolSemester($request, $schoolSemesterId){
        $currentSchool = $request->attributes->get("currentSchool");
        $deleteSchoolSemester = $this->schoolSemesterService->deleteSchoolSemester($schoolSemesterId, $currentSchool);
        return ApiResponseService::success("School Deleted Sucessfully", $deleteSchoolSemester, null, 200);
    }

    public function updateSchoolSemester(UpdateSchoolSemesterRequest $request, $schoolSemesterId){
        $currentSchool = $request->attributes->get("currentSchool");
        $updateSchoolSemester = $this->schoolSemesterService->updateSchoolSemester($request->validated(), $currentSchool, $schoolSemesterId);
        return ApiResponseService::success("School Semester Updated Successfully", $updateSchoolSemester, null, 200);
    }

    public function getSchoolSemester(Request $request){
        $currentSchool = $request->attributes->get("currentSchool");
        $getSchoolSemesters = $this->schoolSemesterService->getSchoolSemesters($currentSchool);
        return ApiResponseService::success("School Semester Fetched Sucessfully", $getSchoolSemesters, null, 200);
    }

}
