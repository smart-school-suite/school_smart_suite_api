<?php

namespace App\Http\Controllers\Auth\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Models\Schooladmin;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\OTP;
use App\Models\PasswordResetToken;
use App\Http\Requests\OtpRequest;
use App\Services\Auth\SchoolAdmin\SchoolAdminPasswordResetService;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\ChangePasswordUnAuthRequest;

class PasswordResetController extends Controller
{
    //

    protected SchoolAdminPasswordResetService $schoolAdminPasswordResetService;
    public function __construct(SchoolAdminPasswordResetService $schoolAdminPasswordResetService)
    {
        $this->schoolAdminPasswordResetService = $schoolAdminPasswordResetService;
    }
    public function resetSchoolAdminPassword(ResetPasswordRequest $request)
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
    public function changeShoolAdminPasswordUnAuthenticated(ChangePasswordUnAuthRequest $request)
    {
        $password_reset_token = $request->header('PASSWORD_RESET_TOKEN');
        $this->schoolAdminPasswordResetService->changeSchoolAdminPasswordUnAuthenticated($request->validated(), $password_reset_token);
    }
}
