<?php

namespace App\Http\Controllers\Auth\Teacher;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Http\Requests\Auth\ChangePasswordUnAuthenticatedRequest;
use App\Services\Auth\Teacher\ResetTeacherPasswordService;
use App\Http\Requests\Auth\PasswordResetRequest;
use App\Http\Requests\Auth\OtpRequest;
class ResetPasswordController extends Controller
{
    protected ResetTeacherPasswordService $resetTeacherPasswordService;
    public function __construct(ResetTeacherPasswordService $resetTeacherPasswordService){
        $this->resetTeacherPasswordService = $resetTeacherPasswordService;
    }
    public function resetInstructorPassword(PasswordResetRequest $request)
    {
        $resetPassword = $this->resetTeacherPasswordService->resetPassword($request->validated());
        return ApiResponseService::success("OTP sent successfully", $resetPassword, null, 200);
    }

    public function verifyInstructorOtp(OtpRequest $request)
    {
        $token_header = $request->header('otp-token-header');
        $verifyOtp = $this->resetTeacherPasswordService->verifyOtp($request->otp, $token_header);
        return ApiResponseService::success("OTP verified Sucessfully", $verifyOtp, null, 200);
    }
    public function ChangeInstructorPasswordUnAuthenticated(ChangePasswordUnAuthenticatedRequest $request)
    {
        $password_reset_token = $request->header('password-reset-token');
        $this->resetTeacherPasswordService->changeInstructorPasswordUnAuthenticated($request->validated(), $password_reset_token);
    }
}
