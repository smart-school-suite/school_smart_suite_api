<?php

namespace App\Http\Controllers\Auth\Student;

use App\Http\Controllers\Controller;
use App\Models\OTP;
use App\Models\Student;
use Illuminate\Support\Str;
use App\Http\Requests\OtpRequest;
use App\Services\ApiResponseService;
use Carbon\Carbon;
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
    public function verify_otp(OtpRequest $request)
    {
        $token_header = $request->header('OTP_TOKEN_HEADER');
        $verifyOtp = $this->validateOtpService->verifyOtp($token_header, $request->otp);
        return ApiResponseService::success("OTP verified Sucessfully OTP verification complete", $verifyOtp, null, 200);
    }
    public function request_another_code(Request $request)
    {
        $token_header = $request->header('OTP_TOKEN_HEADER');
        $requestNewOtp = $this->validateOtpService->requestOtp($token_header);
        return ApiResponseService::success("OTP token sent successfully", $requestNewOtp, null, 200);
    }
}
