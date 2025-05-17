<?php

namespace App\Http\Controllers;
use App\Http\Requests\SchoolSemester\CreateSchoolSemesterRequest;
use App\Http\Requests\SchoolSemester\SchoolSemesterIdRequest;
use App\Http\Requests\SchoolSemester\UpdateSchoolSemesterRequest;
use App\Http\Requests\SchoolSemester\BulkUpdateSchoolSemesterRequest;
use App\Services\ApiResponseService;
use App\Services\SchoolSemesterService;
use Exception;
use Illuminate\Http\Request;

class SchoolSemesterController extends Controller
{
    //
    protected SchoolSemesterService $schoolSemesterService;
    public function __construct(SchoolSemesterService $schoolSemesterService){
        $this->schoolSemesterService = $schoolSemesterService;
    }

    public function createSchoolSemester(CreateSchoolSemesterRequest $request){
        $currentSchool = $request->attributes->get("currentSchool");
        $createSchoolSemester = $this->schoolSemesterService->createSchoolSemester($request->validated(), $currentSchool);
        return ApiResponseService::success("School Semester Created Succesfully", $createSchoolSemester, null, 201);
    }

    public function deleteSchoolSemester(Request $request, $schoolSemesterId){
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

    public function getSchoolSemesterDetails(Request $request, $schoolSemesterId){
        $currentSchool = $request->attributes->get("currentSchool");
        $getSchoolSemesterDetails = $this->schoolSemesterService->getSchoolSemesterDetail($currentSchool, $schoolSemesterId);
        return ApiResponseService::success("School Semester Details Fetched Successfully", $getSchoolSemesterDetails, null, 200);
    }

    public function bulkUpdateSchoolSemester(BulkUpdateSchoolSemesterRequest $request){
         try{
            $bulkUpdateSchoolSemester = $this->schoolSemesterService->bulkUpdateSchoolSemester($request->school_semester);
            return ApiResponseService::success("School Semester Updated Successfully", $bulkUpdateSchoolSemester, null, 200);
         }
         catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
         }
    }

    public function bulkDeleteSchoolSemester(SchoolSemesterIdRequest $request){
        try{
           $bulkDeleteSchoolSemester = $this->schoolSemesterService->bulkDeleteSchoolSemester($request->schoolSemesterIds);
           return ApiResponseService::success("School Semester Deleted Successfully", $bulkDeleteSchoolSemester, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function getActiveSchoolSemesters(Request $request){
         $currentSchool = $request->attributes->get("currentSchool");
        $activeSchoolSemesters = $this->schoolSemesterService->getActiveSchoolSemesters($currentSchool);
        return ApiResponseService::success("Active School Semesters Fetched Successfully", $activeSchoolSemesters, null, 200);
    }

}
