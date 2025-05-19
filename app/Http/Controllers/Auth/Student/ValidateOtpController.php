<?php

namespace App\Http\Controllers\Auth\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\OtpRequest;
use App\Services\ApiResponseService;
use App\Services\Auth\Student\ValidateOtpService;
use Illuminate\Http\Request;

class ValidateOtpController extends Controller
{
    //
    protected ValidateOtpService $validateOtpService;
    public function __construct(ValidateOtpService $validateOtpService)
    {
        $this->validateOtpService = $validateOtpService;
    }
    public function verifyOtp(OtpRequest $request)
    {
        $tokenheader = $request->header('otp-token-header');
        $verifyOtp = $this->validateOtpService->verifyOtp($tokenheader, $request->otp);
        return ApiResponseService::success("OTP verified Sucessfully OTP verification complete", $verifyOtp, null, 200);
    }
    public function request_another_code(Request $request)
    {
        $token_header = $request->header('OTP_TOKEN_HEADER');
        $requestNewOtp = $this->validateOtpService->requestOtp($token_header);
        return ApiResponseService::success("OTP token sent successfully", $requestNewOtp, null, 200);
    }
}
