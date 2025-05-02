<?php

namespace App\Http\Controllers\Auth\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Services\Auth\SchoolAdmin\SchoolAdminPasswordResetService;
use App\Http\Requests\Auth\PasswordResetRequest;
use App\Http\Requests\Auth\OtpRequest;
use App\Http\Requests\Auth\ChangePasswordUnAuthenticatedRequest;

class PasswordResetController extends Controller
{
    //

    protected SchoolAdminPasswordResetService $schoolAdminPasswordResetService;
    public function __construct(SchoolAdminPasswordResetService $schoolAdminPasswordResetService)
    {
        $this->schoolAdminPasswordResetService = $schoolAdminPasswordResetService;
    }
    public function resetSchoolAdminPassword(PasswordResetRequest $request)
    {
        $resetPassword = $this->schoolAdminPasswordResetService->resetPassword($request->validated());
        return ApiResponseService::success("OTP token sent successfully", $resetPassword, null, 200);
    }
    public function verifySchoolAdminOtp(OtpRequest $request)
    {
        $token_header = $request->header('OTP_TOKEN_HEADER');
        $verifyOtp = $this->schoolAdminPasswordResetService->verifyOtp($request->otp, $token_header);
        return ApiResponseService::success("OTP token verified Successfully", $verifyOtp, null, 200);
    }
    public function changeShoolAdminPasswordUnAuthenticated(ChangePasswordUnAuthenticatedRequest $request)
    {
        $password_reset_token = $request->header('PASSWORD_RESET_TOKEN');
        $this->schoolAdminPasswordResetService->changeSchoolAdminPasswordUnAuthenticated($request->validated(), $password_reset_token);
        return ApiResponseService::success("Password Changed Successfully", null, null, 200);
    }
}
