<?php

namespace App\Http\Controllers\Auth\Student;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Http\Requests\Auth\OtpRequest;
use App\Http\Requests\Auth\ChangePasswordUnAuthenticatedRequest;
use App\Http\Requests\Auth\PasswordResetRequest;
use App\Services\Auth\Student\ResetStudentPasswordService;

class ResetPasswordController extends Controller
{
    //
    protected ResetStudentPasswordService $resetStudentPasswordService;
    public function __construct(ResetStudentPasswordService $resetStudentPasswordService)
    {
        $this->resetStudentPasswordService = $resetStudentPasswordService;
    }
    public function resetStudentPassword(PasswordResetRequest $request)
    {
        $resetPassword = $this->resetStudentPasswordService->resetPassword($request->validated());
        return ApiResponseService::success("OTP sent successfully to email", $resetPassword, null, 200);
    }
    public function verifyStudentOtp(OtpRequest $request)
    {
        $token_header = $request->header('OTP_TOKEN_HEADER');
        $verifyOtp = $this->resetStudentPasswordService->verifyOtp($request->otp, $token_header);
        return ApiResponseService::success("OTP verified Succesfully", $verifyOtp, null, 200);
    }
    public function changeStudentPasswordUnAuthenticated(ChangePasswordUnAuthenticatedRequest $request)
    {
        $password_reset_token = $request->header('PASSWORD_RESET_TOKEN');
        $this->resetStudentPasswordService->changeStudentPasswordUnAuthenticated($request->validated, $password_reset_token);
    }
}
