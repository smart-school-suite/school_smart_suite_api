<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\SchoolBranch\UpdateSchoolBranchRequest;
use App\Services\ApiResponseService;
use App\Services\School\SchoolBranchService;

class SchoolBranchController extends Controller
{
       protected SchoolBranchService $schoolBranchesService;
    public function __construct(SchoolBranchService $schoolBranchesService){
        $this->schoolBranchesService = $schoolBranchesService;
    }

    public function updateSchoolBranch(UpdateSchoolBranchRequest $request){
        $currentSchool = $request->attributes->get("currentSchool");
        $updateSchoolBranch = $this->schoolBranchesService->updateSchoolBranch($request->validated(), $currentSchool->id);
        return ApiResponseService::success("School Branch updated Succesfully", $updateSchoolBranch, null, 200);
    }

    public function getBranchDetails(Request $request){
         $currentSchool = $request->attributes->get("currentSchool");
         $branchDetails = $this->schoolBranchesService->getSchoolBranchDetails($currentSchool->id);
         return ApiResponseService::success("School Branch Details Fetched Successfully", $branchDetails, null, 200);
    }

    public function deleteSchoolBranch(Request $request){
        $currentSchool = $request->attributes->get("currentSchool");
        $deleteSchoolBranch = $this->schoolBranchesService->deleteSchoolBranch($currentSchool->id);
       return ApiResponseService::success("School Branch Deleted Sucessfully", $deleteSchoolBranch, null, 200);
    }
}
