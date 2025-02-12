<?php

namespace App\Services;
use App\Models\Schoolbranches;
use Illuminate\Support\Str;
class SchoolBranchesService
{
    // Implement your logic here

    public function createSchoolBranch(array $data){
        $new_school_branch_instance = new Schoolbranches();
        $random_id = Str::uuid()->toString();
        $new_school_branch_instance->id = $random_id;
        $new_school_branch_instance->school_id = $data["school_id"];
        $new_school_branch_instance->branch_name = $data["branch_name"];
        $new_school_branch_instance->address = $data["address"];
        $new_school_branch_instance->city = $data["city"];
        $new_school_branch_instance->state = $data["state"];
        $new_school_branch_instance->postal_code = $data["postal_code"];
        $new_school_branch_instance->phone_two = $data["phone_two"];
        $new_school_branch_instance->phone_one = $data["phone_one"];
        $new_school_branch_instance->website = $data["website"];
        $new_school_branch_instance->email = $data["email"];
        $new_school_branch_instance->semester_count = $data["semester_count"];
        $new_school_branch_instance->max_gpa = $data["max_gpa"];
        $new_school_branch_instance->save();

        return $random_id;
    }

    public function updateSchoolBranch(array $data, $schoolBranchId){
        $schoolBranchExist = Schoolbranches::find($schoolBranchId);
        if(!$schoolBranchExist){
            return ApiResponseService::error("School Branch Not Found", null, 404);
        }
        $filterData = array_filter($data);
        $schoolBranchExist->update($filterData);
        return $schoolBranchExist;
    }

    public function getSchoolBranches(){
        $schoolBranches = Schoolbranches::with('schools')->get();
        return $schoolBranches;
    }

    public function deleteSchoolBranch($schoolBranchId){
        $schoolBranchExist = Schoolbranches::find($schoolBranchId);
        if(!$schoolBranchExist){
            return ApiResponseService::error("School Branch not found", null,404);
        }
        $schoolBranchExist->delete();
        return $schoolBranchExist;
    }

}
