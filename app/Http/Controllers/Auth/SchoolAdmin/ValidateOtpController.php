<?php

namespace App\Http\Controllers\Auth\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Http\Requests\Auth\OtpRequest;
use App\Services\Auth\SchoolAdmin\ValidateOtpService;
use Illuminate\Http\Request;

class ValidateOtpController extends Controller
{
    protected ValidateOtpService $validateOtpService;
    public function __construct(ValidateOtpService $validateOtpService)
    {
        $this->validateOtpService = $validateOtpService;
    }
    public function verifySchoolAdminOtp(OtpRequest $request)
    {
        $tokenHeader = $request->header('X-OTP-Token');
        $verifyOtp = $this->validateOtpService->verifyOtp($tokenHeader, $request->otp);
        return ApiResponseService::success("OTP token verified Succesfully", $verifyOtp, null, 200);
    }
    public function requestNewCode(Request $request)
    {
        $tokenHeader = $request->header('X-OTP-Token');
        $requestNewOtp = $this->validateOtpService->requestOtp($tokenHeader);
        return ApiResponseService::success("OTP token sent succesfully to email", $requestNewOtp, null, 200);
    }
}
