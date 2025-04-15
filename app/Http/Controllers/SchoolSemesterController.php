<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkUpdateSchooolSemesterRequest;
use App\Http\Requests\SchoolSemesterRequest;
use App\Http\Requests\UpdateSchoolSemesterRequest;
use App\Services\ApiResponseService;
use Illuminate\Support\Facades\Validator;
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

    public function getSchoolSemesterDetails(Request $request, $schoolSemesterId){
        $currentSchool = $request->attributes->get("currentSchool");
        $getSchoolSemesterDetails = $this->schoolSemesterService->getSchoolSemesterDetail($currentSchool, $schoolSemesterId);
        return ApiResponseService::success("School Semester Details Fetched Successfully", $getSchoolSemesterDetails, null, 200);
    }

    public function bulkUpdateSchoolSemester(BulkUpdateSchooolSemesterRequest $request){
         try{
            $bulkUpdateSchoolSemester = $this->schoolSemesterService->bulkUpdateSchoolSemester($request->school_semester);
            return ApiResponseService::success("School Semester Updated Successfully", $bulkUpdateSchoolSemester, null, 200);
         }
         catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
         }
    }

    public function bulkDeleteSchoolSemester($schoolSemesterIds){
        $idsArray = explode(',', $schoolSemesterIds);

        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
            return ApiResponseService::error("No IDs provided", null, 422);
        }
        $validator = Validator::make(['ids' => $idsArray], [
            'ids' => 'required|array',
            'ids.*' => 'string|exists:school_semesters,id',
        ]);
        if ($validator->fails()) {
            return ApiResponseService::error($validator->errors(), null, 422);
        }
        try{
           $bulkDeleteSchoolSemester = $this->schoolSemesterService->bulkDeleteSchoolSemester($idsArray);
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
