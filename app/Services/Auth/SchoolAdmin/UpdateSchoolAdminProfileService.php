<?php

namespace App\Services\Auth\SchoolAdmin;

use App\Models\Schooladmin;

class UpdateSchoolAdminProfileService
{
    public function updateSchoolAdminProfile($updateData, $currentSchool){
        $authSchoolAdmin = auth()->guard('schooladmin')->user();
        $schoolAdmin = Schooladmin::where("school_branch_id", $currentSchool->id)->find($authSchoolAdmin->id);
        if($authSchoolAdmin){
            $filteredData = array_filter($updateData);
            $schoolAdmin->update($filteredData);
        }
    }
}
