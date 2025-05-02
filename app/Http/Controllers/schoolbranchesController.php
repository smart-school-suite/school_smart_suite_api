<?php

namespace App\Http\Controllers;
use App\Services\SchoolBranchesService;
use App\Http\Requests\SchoolBranch\CreateSchoolBranchRequest;
use App\Http\Requests\SchoolBranch\UpdateSchoolBranchRequest;
use App\Http\Requests\SchoolBranch\BulkUpdateSchoolBranchRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class SchoolBranchesController extends Controller
{
    //review update request and add validation when updating
    protected SchoolBranchesService $schoolBranchesService;
    public function __construct(SchoolBranchesService $schoolBranchesService){
        $this->schoolBranchesService = $schoolBranchesService;
    }
    public function createSchoolBranch(CreateSchoolBranchRequest $request){
         $createSchoolBranch = $this->schoolBranchesService->createSchoolBranch($request->validated());
         return ApiResponseService::success("School Branch Created Succesfully", $createSchoolBranch, null, 201);
    }

    public function updateSchoolBranch(UpdateSchoolBranchRequest $request, $branch_id){
        $updateSchoolBranch = $this->schoolBranchesService->updateSchoolBranch($request->validated(), $branch_id);
        return ApiResponseService::success("School Branch updated Succesfully", $updateSchoolBranch, null, 200);
    }

    public function getAllSchoolBranches(Request $request){
        $getSchoolBranches = $this->schoolBranchesService->getSchoolBranches();
        return ApiResponseService::success("School Branches Fetched Succefully", $getSchoolBranches, null, 200);
    }

    public function deleteSchoolBranch(Request $request, $branch_id){
        $deleteSchoolBranch = $this->schoolBranchesService->deleteSchoolBranch($branch_id);
       return ApiResponseService::success("School Branch Deleted Sucessfully", $deleteSchoolBranch, null, 200);
    }


}
