<?php

namespace App\Services\Auth\Guardian;
use App\Models\Parents;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\ApiResponseService;
use App\Models\OTP;

class LoginParentService
{
    // Implement your logic here
    public function loginParent($loginData){
        $user = Parents::where('email', $loginData['email'])->first();

        if (!$user || !Hash::check($loginData['password'], $user->password)) {
            return ApiResponseService::error("The provided Credentials Incorrect", null, 400);
        }

        $otp = Str::random(6);

        $otp_header = Str::random(24);

        $expiresAt = Carbon::now()->addMinutes(5);

        OTP::create([
            'token_header' => $otp_header,
            'actorable_id' => $user->id,
            'actorable_type' => 'App\Models\Parents',
            'otp' => $otp,
            'expires_at' => $expiresAt,
        ]);

        return ['opt'=>$otp,'otp_token_header'=>$otp_header];
    }
}
