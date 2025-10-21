<?php

namespace App\Services\Auth\Student;

use App\Models\OTP;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\SchoolBranchApiKey;
use App\Models\Student;
use App\Exceptions\AppException;

class ValidateOtpService
{

    public function verifyOtp($tokenHeader, $otp)
    {
        $otpRecord = OTP::where('otp', $otp)
            ->where('token_header', $tokenHeader)
            ->first();

        if (!$otpRecord) {
            throw new AppException(
                "OTP record not found for token header '{$tokenHeader}' and provided OTP.",
                400,
                "Invalid Verification Code 🛑",
                "The verification code (OTP) or the session token is incorrect. Please double-check the code you entered or try requesting a new one.",
                null
            );
        }

        if ($otpRecord->isExpired()) {
            $otpRecord->delete();
            throw new AppException(
                "OTP record expired for token header '{$tokenHeader}'.",
                400,
                "Verification Code Expired ⏱️",
                "Your verification code has expired. Please request a new code and enter it quickly.",
                null
            );
        }

        $user = Student::where('id', $otpRecord->actorable_id)->first();

        if (!$user) {
            $otpRecord->delete();
            throw new AppException(
                "Associated student (actorable_id: {$otpRecord->actorable_id}) not found for valid OTP record.",
                500,
                "User Data Error 🚨",
                "A system error occurred: your verification code was valid, but we couldn't find your student account. Please contact support immediately.",
                null
            );
        }

        $apiKey = SchoolBranchApiKey::where("school_branch_id", $user->school_branch_id)->first();
        if (!$apiKey) {
            throw new AppException(
                "School Branch API Key missing for school ID '{$user->school_branch_id}' during student login.",
                500,
                "Configuration Error ⚙️",
                "Your school's necessary configuration key is missing. Please contact your school administrator or system support.",
                null
            );
        }

        $token = $user->createToken('studentToken')->plainTextToken;
        $otpRecord->delete();

        return ['authToken' => $token, 'apiKey' => $apiKey->api_key];
    }

    public function requestOtp($otpTokenHeader)
    {
        $otpRecord = OTP::where('token_header', $otpTokenHeader)->first();

        if (!$otpRecord) {
            throw new AppException(
                "OTP record not found for token header '{$otpTokenHeader}'. Cannot generate new OTP.",
                400,
                "Invalid OTP Request 🛑",
                "We cannot process your request for a new verification code. This usually happens if the initial sign-in attempt was invalid or expired. Please start the sign-in process again.",
                null
            );
        }

        $newOtp = Str::random(6);
        $expiresAt = Carbon::now()->addMinutes(5);

        try {
            $otpRecord->update([
                'otp' => $newOtp,
                'expires_at' => $expiresAt,
            ]);
        } catch (\Exception $e) {
            throw new AppException(
                "Failed to update OTP record for token header '{$otpTokenHeader}'. Error: " . $e->getMessage(),
                500,
                "OTP Generation Failed ⚠️",
                "We ran into a system problem while trying to generate a new verification code for you. Please try again in a moment.",
                null
            );
        }

        return $newOtp;
    }
}
