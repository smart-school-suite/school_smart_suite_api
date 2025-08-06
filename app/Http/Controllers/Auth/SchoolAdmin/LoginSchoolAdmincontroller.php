<?php

namespace App\Http\Controllers\Auth\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Services\Auth\SchoolAdmin\LoginSchoolAdminService;
use App\Http\Requests\Auth\LoginSchoolAdminRequest;
use Exception;

class LoginSchoolAdmincontroller extends Controller
{
    //
    protected LoginSchoolAdminService $loginSchoolAdminService;
    public function __construct(LoginSchoolAdminService $loginSchoolAdminService)
    {
        $this->loginSchoolAdminService = $loginSchoolAdminService;
    }
    public function loginShoolAdmin(LoginSchoolAdminRequest $request)
    {
        try{
            $loginSchoolAdmin = $this->loginSchoolAdminService->loginSchoolAdmin($request->validated());
            return ApiResponseService::success("OTP sent successfully to your email", $loginSchoolAdmin, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }

    }
}
