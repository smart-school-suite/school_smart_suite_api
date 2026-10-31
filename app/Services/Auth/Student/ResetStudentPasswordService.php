<?php

namespace App\Services\Auth\Student;

use App\Exceptions\AuthException;
use App\Jobs\AuthenticationJobs\SendOTPViaEmailJob;
use App\Models\OTP;
use App\Models\PasswordResetToken;
use App\Models\Student;
use App\Services\Auth\Student\Concerns\EnsuresStudentCanAuthenticate;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Hash;

class ResetStudentPasswordService
{
    use EnsuresStudentCanAuthenticate;

    public function resetPassword(array $passwordResetData)
    {
        try {
            if (empty($passwordResetData['email'])) {
                throw new AuthException(
                    "Email is required for password reset.",
                    400,
                    "Validation Error",
                    "Please provide the email address associated with your account."
                );
            }

            $student = Student::where('email', $passwordResetData['email'])->first();

            if (!$student) {
                throw new AuthException(
                    "The email you entered is not registered.",
                    401,
                    "Authentication Failed",
                    "This email address is not in our records. Please check for typos or contact your school administration."
                );
            }

            $this->ensureStudentCanAuthenticate($student);

            $otp = (string) random_int(100000, 999999);
            $otpHeader = bin2hex(random_bytes(32));
            $expiresAt = Carbon::now()->addMinutes((int) config('auth.otp_expiry_minutes', 5));

            OTP::where('actorable_id', $student->id)
                ->where('actorable_type', Student::class)
                ->delete();

            OTP::create([
                'token_header'   => Hash::make($otpHeader),
                'actorable_id'   => $student->id,
                'actorable_type' => Student::class,
                'otp'            => Hash::make($otp),
                'expires_at'     => $expiresAt,
            ]);

            SendOTPViaEmailJob::dispatch($student->email, $otp);

            return ['otp_token_header' => $otpHeader];
        } catch (AuthException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AuthException("An unexpected error occurred during password reset. Please try again.", 500, "Server Error", "An unexpected system error occurred. We are investigating the issue.");
        }
    }

    public function verifyOtp(?string $otp, ?string $tokenHeader)
    {
        try {
            if (empty($otp) || empty($tokenHeader)) {
                throw new AuthException(
                    "OTP and token header are required.",
                    400,
                    "Validation Error",
                    "Both the OTP and a valid token header must be provided to proceed."
                );
            }

            $otpRecords = OTP::where('actorable_type', Student::class)->get();

            $matchedRecord = $otpRecords->first(function ($record) use ($tokenHeader, $otp) {
                return Hash::check($tokenHeader, $record->token_header) && Hash::check($otp, $record->otp);
            });

            if (!$matchedRecord) {
                throw new AuthException(
                    "The OTP or token header provided is invalid.",
                    401,
                    "Verification Failed",
                    "The code you entered is incorrect or has already been used. Please check your inputs or request a new code."
                );
            }

            if ($matchedRecord->expires_at && Carbon::parse($matchedRecord->expires_at)->isPast()) {
                $matchedRecord->delete();
                throw new AuthException(
                    "OTP_EXPIRED",
                    401,
                    "Expired OTP",
                    "The verification code has expired. Please request a new code to continue."
                );
            }

            PasswordResetToken::where('actorable_id', $matchedRecord->actorable_id)
                ->where('actorable_type', Student::class)
                ->delete();

            $passwordResetToken = bin2hex(random_bytes(32));

            PasswordResetToken::create([
                'token'          => Hash::make($passwordResetToken),
                'actorable_id'   => $matchedRecord->actorable_id,
                'actorable_type' => Student::class,
                'expires_at'     => Carbon::now()->addHours(1),
            ]);

            $matchedRecord->delete();

            return ['reset_token' => $passwordResetToken];
        } catch (AuthException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AuthException("An unexpected error occurred during OTP verification. Please try again.", 500, "Server Error", "An unexpected system error occurred. We are investigating the issue.");
        }
    }

    public function changeStudentPasswordUnAuthenticated(array $passwordData, ?string $passwordResetToken)
    {
        try {
            if (empty($passwordResetToken)) {
                throw new AuthException(
                    "A password reset token is required.",
                    400,
                    "Validation Error",
                    "Please restart the password reset process to obtain a valid reset token."
                );
            }

            if (empty($passwordData['new_password'])) {
                throw new AuthException(
                    "The new password cannot be empty.",
                    400,
                    "Validation Error",
                    "Please provide a new password to continue."
                );
            }

            if (strlen($passwordData['new_password']) < 8) {
                throw new AuthException(
                    "Password must be at least 8 characters long.",
                    400,
                    "Validation Error",
                    "Please choose a password with a minimum of 8 characters."
                );
            }

            $tokenRecords = PasswordResetToken::where('actorable_type', Student::class)->get();

            $matchedRecord = $tokenRecords->first(function ($record) use ($passwordResetToken) {
                return Hash::check($passwordResetToken, $record->token);
            });

            if (!$matchedRecord) {
                throw new AuthException(
                    "The password reset token provided is invalid.",
                    401,
                    "Authentication Failed",
                    "The reset token is invalid or has already been used. Please restart the password reset process."
                );
            }

            if ($matchedRecord->expires_at && Carbon::parse($matchedRecord->expires_at)->isPast()) {
                $matchedRecord->delete();
                throw new AuthException(
                    "RESET_TOKEN_EXPIRED",
                    401,
                    "Expired Token",
                    "This password reset token has expired. Please restart the password reset process to receive a new one."
                );
            }

            $student = Student::find($matchedRecord->actorable_id);

            if (!$student) {
                $matchedRecord->delete();
                throw new AuthException(
                    "Could not find the associated user.",
                    404,
                    "User Not Found",
                    "The student associated with this password reset token could not be found."
                );
            }

            try {
                $this->ensureStudentCanAuthenticate($student);
            } catch (AuthException $e) {
                $matchedRecord->delete();
                throw $e;
            }

            $student->password = Hash::make($passwordData['new_password']);
            $student->save();

            $matchedRecord->delete();

            return true;
        } catch (AuthException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AuthException("An unexpected error occurred while resetting password. Please try again.", 500, "Server Error", "An unexpected system error occurred. We are investigating the issue.");
        }
    }
}
