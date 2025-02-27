<?php

namespace App\Services\Auth\SchoolAdmin;
use App\Services\ApiResponseService;
class LogoutSchoolAdminService
{
    // Implement your logic here
    public function logoutSchoolAdmin($request){
        $request->user()->currentAccessToken()->delete();
        return ApiResponseService::success("Logout Successfull", null, null, 200);
    }
}
