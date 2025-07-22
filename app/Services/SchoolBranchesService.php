<?php

namespace App\Services;

use App\Models\GradesCategory;
use App\Models\Schoolbranches;
use App\Models\SchoolGradesConfig;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SchoolBranchesService
{
    // Implement your logic here

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
