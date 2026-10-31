<?php

namespace App\Http\Controllers\Auth\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordUnAuthenticatedRequest;
use App\Http\Requests\Auth\OtpRequest;
use App\Http\Requests\Auth\PasswordResetRequest;
use App\Services\ApiResponseService;
use App\Services\Auth\Student\ResetStudentPasswordService;

class ResetPasswordController extends Controller
{
    protected ResetStudentPasswordService $resetStudentPasswordService;

    public function __construct(ResetStudentPasswordService $resetStudentPasswordService)
    {
        $this->resetStudentPasswordService = $resetStudentPasswordService;
    }

    public function resetStudentPassword(PasswordResetRequest $request)
    {
        $resetPassword = $this->resetStudentPasswordService->resetPassword($request->validated());
        return ApiResponseService::success("OTP token sent successfully", $resetPassword, null, 200);
    }

    public function verifyStudentOtp(OtpRequest $request)
    {
        // X-OTP-Token is the standard header; OTP-TOKEN-HEADER is accepted for older clients.
        $tokenHeader = $request->header('X-OTP-Token') ?? $request->header('OTP-TOKEN-HEADER');
        $verifyOtp = $this->resetStudentPasswordService->verifyOtp($request->otp, $tokenHeader);
        return ApiResponseService::success("OTP token verified Successfully", $verifyOtp, null, 200);
    }

    public function changeStudentPasswordUnAuthenticated(ChangePasswordUnAuthenticatedRequest $request)
    {
        // X-PASSWORD-RESET-Token is the standard header; PASSWORD-RESET-TOKEN is accepted for older clients.
        $passwordResetToken = $request->header('X-PASSWORD-RESET-Token') ?? $request->header('PASSWORD-RESET-TOKEN');
        $this->resetStudentPasswordService->changeStudentPasswordUnAuthenticated($request->validated(), $passwordResetToken);
        return ApiResponseService::success("Password Changed Successfully", null, null, 200);
    }
}
