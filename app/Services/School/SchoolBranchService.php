<?php

namespace App\Services\School;
use App\Services\ApiResponseService;
use App\Models\Schoolbranches;
class SchoolBranchService
{
        /**
     * Updates an existing school branch.
     *
     * @param array $data The data to update the school branch with.
     * @param string $schoolBranchId The ID of the school branch to update.
     */
    public function updateSchoolBranch(array $data, $schoolBranchId)
    {
        $schoolBranchExist = Schoolbranches::find($schoolBranchId);
        if (!$schoolBranchExist) {
            return ApiResponseService::error("School Branch Not Found", null, 404);
        }
        $filterData = array_filter($data);
        $schoolBranchExist->update($filterData);
        return $schoolBranchExist;
    }

    public function deleteSchoolBranch($schoolBranchId)
    {
        $schoolBranchExist = Schoolbranches::find($schoolBranchId);
        if (!$schoolBranchExist) {
            return ApiResponseService::error("School Branch not found", null, 404);
        }
        $schoolBranchExist->delete();
        return $schoolBranchExist;
    }

    public function getSchoolBranchDetails($branchId){
        $schoolBranch = Schoolbranches::with(['school'])->find($branchId);
        return $schoolBranch;
    }
}
