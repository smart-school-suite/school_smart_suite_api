<?php

namespace App\Http\Controllers\Auth\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Http\Requests\Auth\OtpRequest;
use App\Services\Auth\SchoolAdmin\ValidateOtpService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
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
        try{
            $token_header = $request->header('OTP_TOKEN_HEADER');
           $verifyOtp = $this->validateOtpService->verifyOtp($token_header, $request->otp);
           return ApiResponseService::success("OTP token verified Succesfully", $verifyOtp, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, $e->getCode());
        }
        catch(ModelNotFoundException $e){
            return ApiResponseService::error($e->getMessage(), null, $e->getCode());
        }
    }
    public function requestNewCode(Request $request)
    {
        $token_header = $request->header('OTP_TOKEN_HEADER');
        $requestNewOtp = $this->validateOtpService->requestOtp($token_header);
        return ApiResponseService::success("OTP token sent succesfully to email", $requestNewOtp, null, 200);
    }
}
