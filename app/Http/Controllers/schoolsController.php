<?php

namespace App\Http\Controllers;


use App\Services\ApiResponseService;
use App\Http\Requests\School\UpdateSchoolRequest;
use App\Services\SchoolService;

class SchoolsController extends Controller
{
    //
     protected SchoolService $schoolService;
     public function __construct(SchoolService $schoolService){
        $this->schoolService = $schoolService;
     }

    public function updateSchool(UpdateSchoolRequest $request, $schoolId)
    {
        $updateSchool = $this->schoolService->updateSchool($request->validated(), $schoolId);
        return ApiResponseService::success("School Updated Sucessfully", $updateSchool, null, 200);
    }

    public function deleteSchool( $schoolId)
    {
        $deleteSchool = $this->schoolService->deleteSchool($schoolId);
        return ApiResponseService::success("School Deleted Succefully", $deleteSchool, null, 200);
    }


    public function getSchoolDetails( $schoolId){
        $getSchoolDetails = $this->schoolService->getSchoolDetails($schoolId);
        return ApiResponseService::success("School Details Fetched Sucessfully", $getSchoolDetails, null, 200);
    }
}
