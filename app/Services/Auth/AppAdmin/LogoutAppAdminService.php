<?php

namespace App\Services\Auth\AppAdmin;
use App\Services\ApiResponseService;
class LogoutAppAdminService
{
    // Implement your logic here

    public function logoutAppAdmin($request){
        $request->user()->currentAccessToken()->delete();
        return ApiResponseService::success("Logout Successfull", null, null, 200);
    }
}
