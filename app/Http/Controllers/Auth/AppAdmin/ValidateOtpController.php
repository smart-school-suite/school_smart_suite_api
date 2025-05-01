<?php

namespace App\Http\Controllers\Auth\AppAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\OtpRequest;
use App\Services\ApiResponseService;
use App\Services\Auth\AppAdmin\ValidateOtpService;
use Illuminate\Http\Request;

class ValidateOtpController extends Controller
{
    //
    protected ValidateOtpService $validateOtpService;
    public function __construct(ValidateOtpService $validateOtpService){
        $this->validateOtpService = $validateOtpService;
    }
    public function verifyAppAdminOtp(OtpRequest $request)
    {
        $token_header = $request->header('OTP_TOKEN_HEADER');
        $verifyOtp = $this->validateOtpService->validateOtp($token_header, $request->otp);
        return ApiResponseService::success("OTP token verified Succesfully", $verifyOtp, null, 200);
    }
    public function requestNewotpCode(Request $request)
    {
        $token_header = $request->header('OTP_TOKEN_HEADER');
        $requestNewCode = $this->validateOtpService->requestOtp($token_header);
        return ApiResponseService::success("New Otp Sent Succesfully", $requestNewCode, null, 200);
    }
}
