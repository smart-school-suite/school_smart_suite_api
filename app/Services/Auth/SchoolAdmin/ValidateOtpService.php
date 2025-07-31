<?php

namespace App\Services\Auth\SchoolAdmin;
use App\Models\OTP;
use App\Services\ApiResponseService;
use Carbon\Carbon;
use App\Models\Schooladmin;
use App\Models\SchoolBranchApiKey;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
class ValidateOtpService
{
    // Implement your logic here
   public function verifyOtp(string $tokenHeader, string $otp)
    {
        try {

            if (empty($tokenHeader) || empty($otp)) {
                throw new Exception("Token header and OTP are required.", 400);
            }

            $otpRecord = OTP::where('otp', $otp)
                            ->where('token_header', $tokenHeader)
                            ->first();

            if (!$otpRecord) {
                Log::warning('OTP verification failed: Invalid OTP or token header.', [
                    'token_header_attempt' => $tokenHeader,
                    'otp_attempt' => $otp,
                ]);
                throw new Exception("Invalid OTP or token.", 401);
            }

            if ($otpRecord->isExpired()) {
                $otpRecord->delete();
                Log::info('OTP verification failed: Expired OTP.', [
                    'otp_id' => $otpRecord->id,
                    'actorable_id' => $otpRecord->actorable_id,
                    'token_header' => $tokenHeader
                ]);
                throw new Exception("Expired OTP. Please request a new one.", 401);
            }

            $user = Schooladmin::find($otpRecord->actorable_id);

            if (!$user) {
                Log::critical('OTP verification failed: Associated user not found for OTP ID ' . $otpRecord->id, [
                    'otp_record' => $otpRecord->toArray()
                ]);
                $otpRecord->delete();
                throw new Exception("User associated with OTP not found. Please try logging in again.",  500);
            }

            $token = $user->createToken('schoolAdminToken')->plainTextToken;

            $apiKeyRecord = SchoolBranchApiKey::where("school_branch_id", $user->school_branch_id)->first();

            if (!$apiKeyRecord) {
                Log::critical('API Key not found for school branch ID: ' . $user->school_branch_id, [
                    'user_id' => $user->id,
                    'school_branch_id' => $user->school_branch_id
                ]);
                throw new Exception("API key configuration error. Please contact support.", 500);
            }

            $otpRecord->delete();

            return [
                'authToken' => $token,
                'apiKey' => $apiKeyRecord->api_key
            ];

        } catch (ModelNotFoundException $e) {
            Log::error("Database record not found during OTP verification: " . $e->getMessage(), [
                'token_header' => $tokenHeader,
                'otp' => $otp,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
             throw new Exception("Verification failed due to missing data. Please try again.", 500);
        } catch (Exception $e) {
            Log::error("An unexpected error occurred during OTP verification: " . $e->getMessage(), [
                'token_header' => $tokenHeader,
                'otp' => $otp,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new Exception("An unexpected error occurred. Please try again later.", 500);
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
