<?php

namespace App\Http\Controllers\Auth\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\OtpRequest;
use App\Services\ApiResponseService;
use App\Services\Auth\Student\ValidateOtpService;
use Illuminate\Http\Request;

class ValidateOtpController extends Controller
{
    protected ValidateOtpService $validateOtpService;

    public function __construct(ValidateOtpService $validateOtpService)
    {
        $this->validateOtpService = $validateOtpService;
    }

    public function verifyOtp(OtpRequest $request)
    {
        $tokenHeader = $this->otpTokenHeader($request);
        $verifyOtp = $this->validateOtpService->verifyOtp($tokenHeader, $request->otp);
        return ApiResponseService::success("OTP token verified Succesfully", $verifyOtp, null, 200);
    }

    public function requestNewOtp(Request $request)
    {
        $tokenHeader = $this->otpTokenHeader($request);
        $requestNewOtp = $this->validateOtpService->requestOtp($tokenHeader);
        return ApiResponseService::success("OTP token sent succesfully to email", $requestNewOtp, null, 200);
    }

    /**
     * X-OTP-Token is the standard header (same as school admin). The legacy
     * OTP-TOKEN-HEADER name is still accepted for older clients.
     */
    private function otpTokenHeader(Request $request): ?string
    {
        return $request->header('X-OTP-Token') ?? $request->header('OTP-TOKEN-HEADER');
    }
}
