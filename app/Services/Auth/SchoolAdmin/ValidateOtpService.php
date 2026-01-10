<?php

namespace App\Services\Auth\SchoolAdmin;
use App\Models\OTP;
use App\Services\ApiResponseService;
use Carbon\Carbon;
use App\Models\Schooladmin;
use App\Models\SchoolBranchApiKey;
use Illuminate\Support\Str;
use Exception;
use App\Exceptions\AuthException;
class ValidateOtpService
{
    // Implement your logic here
   public function verifyOtp(string $tokenHeader, string $otp)
    {
        try {
            $otpRecord = OTP::where('otp', $otp)
                ->where('token_header', $tokenHeader)
                ->first();

            if (!$otpRecord) {
                throw new AuthException("Invalid OTP or token.", 401, "Verification Failed", "The provided OTP or token header is incorrect. Please double-check your inputs.");
            }

            if ($otpRecord->isExpired()) {
                $otpRecord->delete();
                throw new AuthException("Expired OTP. Please request a new one.", 401, "Expired OTP", "The One-Time Password has expired. Please request a new one to log in.");
            }

            $user = Schooladmin::find($otpRecord->actorable_id);

            if (!$user) {
                $otpRecord->delete();
                throw new AuthException("User associated with OTP not found. Please try logging in again.", 500, "User Not Found", "The user account linked to this OTP could not be located. This issue has been logged.");
            }

            $token = $user->createToken('schoolAdminToken')->plainTextToken;
            $apiKeyRecord = SchoolBranchApiKey::where("school_branch_id", $user->school_branch_id)->first();

            if (!$apiKeyRecord) {
                throw new AuthException("API key configuration error. Please contact support.", 500, "Configuration Error", "A required API key for your school branch is missing. Please contact your administrator.");
            }

            $otpRecord->delete();

            return [
                'authToken' => $token,
                'apiKey' => $apiKeyRecord->api_key
            ];

        } catch (AuthException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AuthException("An unexpected error occurred. Please try again later.", 500, "Server Error", "An unexpected system error occurred during OTP verification. We are investigating the issue.");
        }
    }


    public function requestOtp($otpTokenHeader)
    {

        $otpRecord = OTP::where('token_header', $otpTokenHeader)->first();

        if (!$otpRecord) {
            return ApiResponseService::error("Expired Otp token", null, 400);
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
