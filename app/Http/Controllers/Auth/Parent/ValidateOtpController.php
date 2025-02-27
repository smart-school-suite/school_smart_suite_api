<?php

namespace App\Http\Controllers\Auth\Parent;

use App\Http\Controllers\Controller;
use App\Http\Requests\OtpRequest;
use App\Services\ApiResponseService;
use App\Services\Auth\Guardian\ValidateOtpService;
use Illuminate\Http\Request;

class ValidateOtpController extends Controller
{
    //

    protected ValidateOtpService $validateOtpService;
    public function __construct(ValidateOtpService $validateOtpService){
        $this->validateOtpService = $validateOtpService;
    }
    public function verifyParentOtp(OtpRequest $request)
    {
        $token_header = $request->header('OTP_TOKEN_HEADER');
        $validateOtp = $this->validateOtpService->verifyOtp($token_header, $request->otp);
        return ApiResponseService::success("OTP verified Succesfully", $validateOtp, null, 200);
    }
    public function requestNewOtp(Request $request)
    {
        $token_header = $request->header('OTP_TOKEN_HEADER');
        $requestNewCode = $this->validateOtpService->requestOtp($token_header);
        return ApiResponseService::success("OTP token sent to email succesfully", $requestNewCode, null, 200);
    }
}
