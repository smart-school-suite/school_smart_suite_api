<?php

namespace App\Services\Auth\Guardian;
use App\Services\ApiResponseService;
class LogoutGuardianService
{
    // Implement your logic here
    public function logoutGuardian($request){
        $request->user()->currentAccessToken()->delete();
        return ApiResponseService::success("Logout Successfull", null, null, 200);
    }
}
