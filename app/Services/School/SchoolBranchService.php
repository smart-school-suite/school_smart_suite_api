<?php

namespace App\Services\School;

use App\Services\ApiResponseService;
use App\Models\Schoolbranches;
use App\Events\Actions\AdminActionEvent;

class SchoolBranchService
{
    /**
     * Updates an existing school branch.
     *
     * @param array $data The data to update the school branch with.
     * @param string $schoolBranchId The ID of the school branch to update.
     */
    public function updateSchoolBranch(array $data, $schoolBranchId, $authAdmin)
    {
        $schoolBranchExist = Schoolbranches::find($schoolBranchId);
        if (!$schoolBranchExist) {
            return ApiResponseService::error("School Branch Not Found", null, 404);
        }
        $filterData = array_filter($data);
        $schoolBranchExist->update($filterData);
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.schoolBranch.update"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $schoolBranchId,
                "feature" => "schoolBranchManagement",
                "authAdmin" => $authAdmin,
                "data" => $schoolBranchExist,
                "message" => "School Branch Updated",
            ]
        );
        return $schoolBranchExist;
    }

    public function deleteSchoolBranch($schoolBranchId, $authAdmin)
    {
        $schoolBranchExist = Schoolbranches::find($schoolBranchId);
        if (!$schoolBranchExist) {
            return ApiResponseService::error("School Branch not found", null, 404);
        }
        $schoolBranchExist->delete();
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.schoolBranch.delete"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $schoolBranchId,
                "feature" => "schoolBranchManagement",
                "authAdmin" => $authAdmin,
                "data" => $schoolBranchExist,
                "message" => "School Branch Deleted",
            ]
        );
        return $schoolBranchExist;
    }

    public function getSchoolBranchDetails($branchId)
    {
        $schoolBranch = Schoolbranches::with(['school'])->find($branchId);
        return $schoolBranch;
    }
}
