<?php

namespace App\Services\Auth\Student;
use App\Services\ApiResponseService;
class LogoutStudentService
{
    // Implement your logic here
    public function logoutStudent($request){
        $request->user()->currentAccessToken()->delete();
        return ApiResponseService::success("Logout Successfull", null, null, 200);
    }
}
