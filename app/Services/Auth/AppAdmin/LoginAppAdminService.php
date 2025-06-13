<?php

namespace App\Services\Auth\AppAdmin;

use App\Jobs\AuthenticationJobs\SendOTPViaEmailJob;
use App\Jobs\SendOtpJob;
use Illuminate\Support\Facades\Hash;
use App\Models\Edumanageadmin;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\OTP;
use App\Services\ApiResponseService;

class LoginAppAdminService
{
    // Implement your logic here
    public function loginAppAdmin($loginData){
        $appAdmin = Edumanageadmin::where('email', $loginData['email'])->first();

        if (!$appAdmin || !Hash::check($loginData["password"], $appAdmin->password)) {
            return ApiResponseService::error("The provided Credentials Incorrect", null, 400);
        }

        $otp = Str::random(6);

        $otp_header = Str::random(24);

        $expiresAt = Carbon::now()->addMinutes(5);

        OTP::create([
            'token_header' => $otp_header,
            'actorable_id' => $appAdmin->id,
            'actorable_type' => Edumanageadmin::class,
            'otp' => $otp,
            'expires_at' => $expiresAt,
        ]);
        SendOTPViaEmailJob::dispatch($loginData['email'], $otp);
        return ['otp_token_header'=>$otp_header];
    }
}
