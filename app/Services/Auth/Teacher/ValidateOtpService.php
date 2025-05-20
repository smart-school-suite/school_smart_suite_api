<?php

namespace App\Services\Auth\Teacher;
use App\Services\ApiResponseService;
use App\Models\SchoolBranchApiKey;
use App\Models\OTP;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Teacher;

class ValidateOtpService
{
    // Implement your logic here
    public function verifyOtp($tokenHeader, $otp)
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

        $user = Teacher::where('id', $otpRecord->actorable_id)->first();

        $token = $user->createToken('teacherToken')->plainTextToken;

        $apiKey = SchoolBranchApiKey::where("school_branch_id", $user->school_branch_id)->first();
        $otpRecord->delete();
        return [
            'auth_token' => $token,
            'apiKey' => $apiKey->api_key
        ];
    }


    public function requestOtp($otpTokenHeader)
    {

        $otpRecord = OTP::where('token_header', $otpTokenHeader)->first();

        if (!$otpRecord) {
            return response()->json(['message' => 'Invalid OTP'], 400);
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
