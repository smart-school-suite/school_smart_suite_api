<?php

namespace App\Services\Auth\SchoolAdmin;

use App\Jobs\AuthenticationJobs\SendOTPViaEmailJob;
use App\Models\OTP;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Models\Schooladmin;
use App\Models\PasswordResetToken;
use Exception;
use App\Exceptions\AuthException;

class SchoolAdminPasswordResetService
{
    // Implement your logic here
    public function resetPassword(array $passwordResetData)
    {
        try {
            if (empty($passwordResetData['email'])) {
                throw new AuthException("Email is required for password reset.", 400, "Validation Error", "Please provide the email address associated with your account.");
            }

            $schoolAdminExists = Schooladmin::where('email', $passwordResetData['email'])->first();

            if (!$schoolAdminExists) {
                throw new AuthException("We couldn't find a user with that email address.", 404, "User Not Found", "The email you entered does not match any registered account. Please check for typos or register a new account.");
            }

            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
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
        } catch (AuthException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AuthException("An unexpected error occurred. Please try again.", 500, "Server Error", "An unexpected system error occurred while trying to reset your password.");
        }
    }

    public function verifyOtp(string $otp, string $tokenHeader)
    {
        try {
            if (empty($otp) || empty($tokenHeader)) {
                throw new AuthException("OTP and token header are required.", 400, "Validation Error", "Both the OTP and a valid token header must be provided to proceed.");
            }

            $otpRecord = OTP::where('otp', $otp)
                ->where('token_header', $tokenHeader)
                ->first();

            if (!$otpRecord) {
                throw new AuthException("Invalid OTP or token.", 401, "Verification Failed", "The provided OTP or token header is incorrect. Please double-check your inputs.");
            }

            if ($otpRecord->isExpired()) {
                $otpRecord->delete();
                throw new AuthException("Expired OTP. Please request a new password reset.", 401, "Expired OTP", "The One-Time Password has expired. Please request a new password reset to receive a fresh OTP.");
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

            return ['password_reset_token' => $passwordResetToken];
        } catch (AuthException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AuthException("An unexpected error occurred. Please try again later.", 500, "Server Error", "An unexpected system error occurred during OTP verification. We are investigating the issue.");
        }
    }

    public function changeSchoolAdminPasswordUnAuthenticated(array $passwordData, string $passwordResetToken)
    {
        try {
            if (empty($passwordData['new_password'])) {
                throw new AuthException("The new password cannot be empty.", 400, "Validation Error", "Please provide a new password to continue.");
            }
            if (strlen($passwordData['new_password']) < 8) {
                throw new AuthException("Password must be at least 8 characters long.", 400, "Validation Error", "Please choose a password with a minimum of 8 characters.");
            }

            $resetTokenRecord = PasswordResetToken::where('token', $passwordResetToken)->first();

            if (!$resetTokenRecord) {
                throw new AuthException("Invalid or expired password reset token.", 401, "Authentication Failed", "The provided password reset token is invalid or has already been used. Please restart the password reset process.");
            }

            if (Carbon::now()->greaterThan($resetTokenRecord->expires_at)) {
                $resetTokenRecord->delete();
                throw new AuthException("Expired password reset token.", 401, "Expired Token", "This password reset link has expired. Please restart the password reset process to get a new one.");
            }

            $appAdmin = Schooladmin::find($resetTokenRecord->actorable_id);

            if (!$appAdmin) {
                $resetTokenRecord->delete();
                throw new AuthException("Could not find the associated user.", 500, "User Not Found", "The user associated with this password reset request could not be found. This issue has been logged.");
            }

            $appAdmin->password = Hash::make($passwordData['new_password']);
            $appAdmin->save();

            $resetTokenRecord->delete();

            return true;
        } catch (AuthException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AuthException("An unexpected error occurred. Please try again later.", 500, "Server Error", "An unexpected system error occurred while changing your password.");
        }
    }
}
