<?php

namespace App\Services\Auth\AppAdmin;

use App\Models\OTP;
use App\Services\ApiResponseService;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Edumanageadmin;

class ValidateOtpService
{
    // Implement your logic here

    public function validateOtp($tokenHeader, $otp)
    {

        $otpRecord = OTP::where('otp', $otp)
            ->where('token_header', $tokenHeader)
            ->first();

        if (!$otpRecord) {
            return ApiResponseService::error("Invald OTP token", null, 400);
        }

        if ($otpRecord->isExpired()) {
            return ApiResponseService::error("Expired Otp token", null, 400);
        }

        $user = Edumanageadmin::where('id', $otpRecord->actorable_id)->first();

        $token = $user->createToken('appAdminToken')->plainTextToken;

        $otpRecord->update(['used' => true]);

        $otpRecord->delete();

        return $token;
    }

    public function requestOtp($otpTokenHeader) {

        $otpRecord = OTP::where('token_header', $otpTokenHeader)->first();

        if (!$otpRecord) {
            return ApiResponseService::error("Invald OTP token", null, 400);
        }

        $newOtp = Str::random(6);

        $expiresAt = Carbon::now()->addMinutes(5);

        $otpRecord->update([
            'otp' => $newOtp,
            'expires_at' => $expiresAt,
        ]);

        return $newOtp;
    }
}
