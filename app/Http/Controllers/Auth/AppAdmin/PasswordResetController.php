<?php

namespace App\Http\Controllers\Auth\AppAdmin;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Http\Requests\OtpRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\ChangePasswordUnAuthRequest;
use App\Services\Auth\AppAdmin\AppAdminPasswordResetService;

class PasswordResetController extends Controller
{
    protected AppAdminPasswordResetService $appAdminPasswordResetService;
    public function __construct(AppAdminPasswordResetService $appAdminPasswordResetService)
    {
        $this->appAdminPasswordResetService = $appAdminPasswordResetService;
    }
    public function resetAppAdminPassword(ResetPasswordRequest $request)
    {
        $resetPassword = $this->appAdminPasswordResetService->resetPassword($request->validated());
        return ApiResponseService::success("Opt token sent succesfully", $resetPassword, null, 200);
    }

    public function verifyAppAdminOtp(OtpRequest $request)
    {
        $token_header = $request->header('OTP_TOKEN_HEADER');
        $verifyOtp = $this->appAdminPasswordResetService->verifyOtp($request->otp, $token_header);
        return ApiResponseService::success("OTP validated Succesfully", $verifyOtp, null, 200);
    }
    public function changeAppAdminPasswordUnAuthenticated(ChangePasswordUnAuthRequest $request)
    {
        $this->appAdminPasswordResetService->ChangeAppAdminPasswordUnAuthenticated($request->new_password, $request);
    }
}
