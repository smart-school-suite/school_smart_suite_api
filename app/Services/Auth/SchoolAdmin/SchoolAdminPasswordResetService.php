<?php

namespace App\Services\Auth\SchoolAdmin;
use App\Jobs\AuthenticationJobs\SendOTPViaEmailJob;
use App\Models\OTP;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Models\Schooladmin;
use App\Services\ApiResponseService;
use App\Models\PasswordResetToken;
use Exception;
use Illuminate\Support\Facades\Log;
class SchoolAdminPasswordResetService
{
    // Implement your logic here
    public function resetPassword(array $passwordResetData)
    {
        try {

            if (empty($passwordResetData['email'])) {
                throw new Exception("Email is required for password reset.", 400);
            }

            $schoolAdminExists = Schooladmin::where('email', $passwordResetData['email'])->first();

            if (!$schoolAdminExists) {
              throw new Exception("We couldn't find a user with that email address.", 404);
            }

            $otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

            $otp_header = Str::random(200);

            $expiresAt = Carbon::now()->addMinutes(config('auth.otp_expiry_minutes', 5));

            OTP::where('actorable_id', $schoolAdminExists->id)
                ->where('actorable_type', Schooladmin::class)
                ->delete();

            OTP::create([
                'token_header' => $otp_header,
                'actorable_id' => $schoolAdminExists->id,
                'actorable_type' => Schooladmin::class,
                'otp' => $otp,
                'expires_at' => $expiresAt,
            ]);

            SendOTPViaEmailJob::dispatch($passwordResetData['email'], $otp);

            return ['otp_header' => $otp_header];

        } catch (Exception $e) {
            Log::error("Password reset initiation failed: " . $e->getMessage(), [
                'email' => $passwordResetData['email'] ?? 'N/A',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new Exception("An unexpected error occurred. Please try again.",  500);
        }
    }

    /**
     * Verifies the provided OTP for password reset and issues a password reset token.
     *
     * @param string $otp The One-Time Password.
     * @param string $tokenHeader The header associated with the OTP.
     * @return array|\Illuminate\Http\JsonResponse Returns an array with 'password_reset_token' on success,
     * or a JSON error response via ApiResponseService::error on failure.
     */
    public function verifyOtp(string $otp, string $tokenHeader)
    {
        try {
            if (empty($otp) || empty($tokenHeader)) {
                throw new Exception("OTP and token header are required.", 400);
            }

            $otpRecord = OTP::where('otp', $otp)
                            ->where('token_header', $tokenHeader)
                            ->first();

            if (!$otpRecord) {
                Log::warning('Password reset OTP verification failed: Invalid OTP or token header.', [
                    'token_header_attempt' => $tokenHeader,
                    'otp_attempt' => $otp,
                    'ip_address' => request()->ip(),
                ]);
                throw new Exception("Invalid OTP or token.",  401);
            }

            if ($otpRecord->isExpired()) {
                $otpRecord->delete();
                Log::info('Password reset OTP verification failed: Expired OTP.', [
                    'otp_id' => $otpRecord->id,
                    'actorable_id' => $otpRecord->actorable_id,
                    'token_header' => $tokenHeader
                ]);
                throw new Exception("Expired OTP. Please request a new password reset.", 401);
            }

            PasswordResetToken::where('actorable_id', $otpRecord->actorable_id)
                              ->where('actorable_type', Schooladmin::class)
                              ->delete();

            $passwordResetToken = Str::random(200);

            PasswordResetToken::create([
                'token' => $passwordResetToken,
                'actorable_id' => $otpRecord->actorable_id,
                'actorable_type' => Schooladmin::class,
                'expires_at' => Carbon::now()->addHours(1),
            ]);

            $otpRecord->delete();

            return [
                'password_reset_token' => $passwordResetToken,
            ];

        } catch (Exception $e) {
            Log::error("An unexpected error occurred during password reset OTP verification: " . $e->getMessage(), [
                'otp_attempt' => $otp,
                'token_header_attempt' => $tokenHeader,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new Exception("An unexpected error occurred. Please try again later.",  500);
        }
    }

    /**
     * Changes the school admin's password after successful OTP verification.
     *
     * @param array $passwordData An associative array containing 'new_password' and 'new_password_confirmation'.
     * @param string $passwordResetToken The password reset token obtained from verifyOtp.
     * @return boolean Returns null on success, or a JSON error response on failure.
     */
    public function changeSchoolAdminPasswordUnAuthenticated(array $passwordData, string $passwordResetToken)
    {
        try {

            if (empty($passwordData['new_password'])) {
                throw new Exception("New password and confirmation do not match or are empty.",400);
            }
            if (strlen($passwordData['new_password']) < 8) {
                throw new Exception("Password must be at least 8 characters long.",400);
            }

            $resetTokenRecord = PasswordResetToken::where('token', $passwordResetToken)->first();

            if (!$resetTokenRecord) {
                Log::warning('Password reset change failed: Invalid or used password reset token.', [
                    'token_attempt' => $passwordResetToken,
                    'ip_address' => request()->ip(),
                ]);
                throw new Exception("Invalid or expired password reset token.",  401);
            }

            if (Carbon::now()->greaterThan($resetTokenRecord->expires_at)) {
                $resetTokenRecord->delete();
                Log::info('Password reset change failed: Expired password reset token.', [
                    'reset_token_id' => $resetTokenRecord->id,
                    'actorable_id' => $resetTokenRecord->actorable_id
                ]);
                throw new Exception("Expired password reset token. Please restart the process.",  401);
            }

            $appAdmin = Schooladmin::find($resetTokenRecord->actorable_id);

            if (!$appAdmin) {
                Log::critical('Password reset change failed: Associated school admin not found for token ID ' . $resetTokenRecord->id, [
                    'reset_token_record' => $resetTokenRecord->toArray()
                ]);
                $resetTokenRecord->delete();
               throw new Exception("Could not find the associated user. Please try logging in again.", 500);
            }

            $appAdmin->password = Hash::make($passwordData['new_password']);
            $appAdmin->save();

            $resetTokenRecord->delete();

           return true;

        } catch (Exception $e) {
            Log::error("An unexpected error occurred during password change: " . $e->getMessage(), [
                'password_reset_token_attempt' => $passwordResetToken,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new Exception("An unexpected error occurred. Please try again later.",  500);
        }
    }
}
