<?php

namespace App\Services\Auth\AppAdmin;
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
        $user = Edumanageadmin::where('email', $loginData['email'])->first();

        if (!$user || !Hash::check($loginData["password"], $user->password)) {
            return ApiResponseService::error("The provided Credentials Incorrect", null, 400);
        }

        $otp = Str::random(6);

        $otp_header = Str::random(24);

        $expiresAt = Carbon::now()->addMinutes(5);

        OTP::create([
            'token_header' => $otp_header,
            'actorable_id' => $user->id,
            'actorable_type' => 'App\Models\Edumanageadmin',
            'otp' => $otp,
            'expires_at' => $expiresAt,
        ]);

        return ['opt'=>$otp,'otp_token_header'=>$otp_header];
    }
}
