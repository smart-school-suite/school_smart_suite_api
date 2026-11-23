<?php

namespace App\Services\Auth\SchoolAdmin;

use App\Models\Schoolbranches;

class GetAuthSchoolAdminService
{
    // Implement your logic here
    public function getAuthSchoolAdmin(){
        $authSchoolAdmin = auth()->guard('schooladmin')->user();
        $authSchoolAdmin->load('currentAvatar');
        $getSchoolDetails = Schoolbranches::where("id", $authSchoolAdmin->school_branch_id)
          ->with(['school', 'school.country'])
        ->first();
        return [
            'authSchoolAdmin' => $authSchoolAdmin,
            'schoolDetails' => $getSchoolDetails
        ];
    }
}
