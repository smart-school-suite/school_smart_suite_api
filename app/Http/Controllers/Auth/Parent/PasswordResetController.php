<?php

namespace App\Http\Controllers\Auth\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use App\Models\Parents;
use App\Services\ApiResponseService;
use App\Models\PasswordResetToken;
use Illuminate\Support\Facades\Hash;
use App\Models\OTP;
use Carbon\Carbon;
use App\Http\Requests\OtpRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\ChangePasswordUnAuthRequest;
use App\Services\Auth\Guardian\GuardianPasswordResetService;
use Illuminate\Http\Request;

class PasswordResetController extends Controller
{
    //resetpasswordController
    protected GuardianPasswordResetService $guardianPasswordResetService;
    public function __construct(GuardianPasswordResetService $guardianPasswordResetService){
        $this->guardianPasswordResetService = $guardianPasswordResetService;
    }
    public function resetParentPassword(ResetPasswordRequest $request)
    {
        $resetPassword = $this->guardianPasswordResetService->resetPassword($request->validated());
        return ApiResponseService::success("OTP token sent successfully", $resetPassword, null, 200);
    }
    public function verifyParentOtp(OtpRequest $request)
    {
        $token_header = $request->header('OTP_TOKEN_HEADER');
        $verifyOtp = $this->guardianPasswordResetService->verifyOtp($request->otp, $token_header);
        return ApiResponseService::success("OTP token verified succesfully", $verifyOtp, null, 200);
    }
    public function changeParentPasswordUnAuthenticated(ChangePasswordUnAuthRequest $request)
    {
        $password_reset_token = $request->header('PASSWORD_RESET_TOKEN');
        $this->guardianPasswordResetService->changeGuardianPasswordUnAuthenticated($request->validated(), $password_reset_token);
    }
}
