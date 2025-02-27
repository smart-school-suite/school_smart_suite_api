<?php

namespace App\Services\Auth\Teacher;
use App\Services\ApiResponseService;
class LogoutTeacherService
{
    // Implement your logic here
    public function logoutAdmin($request){
        $request->user()->currentAccessToken()->delete();
        return ApiResponseService::success("Logout Successfull", null, null, 200);
    }
}
