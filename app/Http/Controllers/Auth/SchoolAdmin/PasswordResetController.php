<?php

namespace App\Http\Controllers\Auth\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Services\Auth\SchoolAdmin\SchoolAdminPasswordResetService;
use App\Http\Requests\Auth\PasswordResetRequest;
use App\Http\Requests\Auth\OtpRequest;
use App\Http\Requests\Auth\ChangePasswordUnAuthenticatedRequest;
use Exception;

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
        try {
            $resetPassword = $this->schoolAdminPasswordResetService->resetPassword($request->validated());
            return ApiResponseService::success("OTP token sent successfully", $resetPassword, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, $e->getCode());
        }
    }
    public function verifySchoolAdminOtp(OtpRequest $request)
    {
        try {
            $token_header = $request->header('otp-token-header');
            $verifyOtp = $this->schoolAdminPasswordResetService->verifyOtp($request->otp, $token_header);
            return ApiResponseService::success("OTP token verified Successfully", $verifyOtp, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, $e->getCode());
        }
    }
    public function changeShoolAdminPasswordUnAuthenticated(ChangePasswordUnAuthenticatedRequest $request)
    {
        try {
            $password_reset_token = $request->header('password-reset-token');
            $this->schoolAdminPasswordResetService->changeSchoolAdminPasswordUnAuthenticated($request->validated(), $password_reset_token);
            return ApiResponseService::success("Password Changed Successfully", null, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, $e->getCode());
        }
    }
}
