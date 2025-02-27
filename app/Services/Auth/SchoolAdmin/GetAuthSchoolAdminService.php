<?php

namespace App\Services\Auth\SchoolAdmin;

class GetAuthSchoolAdminService
{
    // Implement your logic here
    public function getAuthSchoolAdmin(){
        $authSchoolAdmin = auth()->guard('schooladmin')->user();
        return $authSchoolAdmin;
    }
}
