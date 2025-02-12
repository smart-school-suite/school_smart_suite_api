<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Http\Requests\SchoolRequest;
use App\Services\ApiResponseService;
use App\Http\Requests\UpdateSchoolRequest;
use App\Services\SchoolService;

class schoolsController extends Controller
{
    //
     protected SchoolService $schoolService;
     public function __construct(SchoolService $schoolService){
        $this->schoolService = $schoolService;
     }
    public function register_school_to_edumanage(SchoolRequest $request)
    {
        $createSchool = $this->schoolService->createSchool($request->validated());
        return ApiResponseService::success("School Created Sucessfully", $createSchool, null, 201);
    }

    public function update_school(UpdateSchoolRequest $request, $school_id)
    {
        $school = School::find($school_id);
        $updateSchool = $this->schoolService->updateSchool($request->validated(), $school_id);
        return ApiResponseService::success("School Updated Sucessfully", $updateSchool, null, 200);
    }

    public function delete_school( $school_id)
    {
        $deleteSchool = $this->schoolService->deleteSchool($school_id);
        return ApiResponseService::success("School Deleted Succefully", $deleteSchool, null, 200);
    }


    public function getSchoolDetails( $schoolId){
        $getSchoolDetails = $this->schoolService->getSchoolDetails($schoolId);
        return ApiResponseService::success("School Details Fetched Sucessfully", $getSchoolDetails, null, 200);
    }
}
