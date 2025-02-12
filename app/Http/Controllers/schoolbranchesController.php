<?php

namespace App\Http\Controllers;
use App\Models\Schoolbranches;
use Illuminate\Support\Str;
use App\Services\SchoolBranchesService;
use App\Http\Requests\CreateSchoolBranchRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class schoolbranchesController extends Controller
{
    //review update request and add validation when updating
    protected SchoolBranchesService $schoolBranchesService;
    public function __construct(SchoolBranchesService $schoolBranchesService){
        $this->schoolBranchesService = $schoolBranchesService;
    }
    public function create_school_branch(CreateSchoolBranchRequest $request){
         $createSchoolBranch = $this->schoolBranchesService->createSchoolBranch($request->validated());
         return ApiResponseService::success("School Branch Created Succesfully", $createSchoolBranch, null, 201);
    }

    public function update_school_branch(Request $request, $branch_id){
        $updateSchoolBranch = $this->schoolBranchesService->updateSchoolBranch($request->validated(), $branch_id);
        return ApiResponseService::success("School Branch updated Succesfully", $updateSchoolBranch, null, 200);
    }

    public function get_all_schoool_branches(Request $request){
        $school_branch_data = Schoolbranches::all();
        $getSchoolBranches = $this->schoolBranchesService->getSchoolBranches();
        return ApiResponseService::success("School Branches Fetched Succefully", $getSchoolBranches, null, 200);
    }

    public function delete_school_branch(Request $request, $branch_id){
        $deleteSchoolBranch = $this->schoolBranchesService->deleteSchoolBranch($branch_id);
       return ApiResponseService::success("School Branch Deleted Sucessfully", $deleteSchoolBranch, null, 200);
    }


}
