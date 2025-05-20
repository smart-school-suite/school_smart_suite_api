<?php

namespace App\Http\Controllers\Auth\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\OtpRequest;
use App\Services\ApiResponseService;
use App\Services\Auth\Teacher\ValidateOtpService;
use Illuminate\Http\Request;

class ValidateOtpController extends Controller
{
    //
    protected ValidateOtpService $validateOtpService;
    public function __construct(ValidateOtpService $validateOtpService){
        $this->validateOtpService = $validateOtpService;
    }
    public function verifyInstructorLoginOtp(OtpRequest $request)
    {
        $tokenHeader = $request->header('otp-token-header');
        $verifyOtp = $this->validateOtpService->verifyOtp($tokenHeader, $request->otp);
        return ApiResponseService::success("OTP token verified Succesfully", $verifyOtp, null, 200);
    }
    public function requestNewOtp(Request $request)
    {
        $token_header = $request->header('opt-token-header');
        $requestNewOtp = $this->validateOtpService->requestOtp($token_header);
        return ApiResponseService::success("OTP code sent successfully", $requestNewOtp, null, 200);
    }
}
