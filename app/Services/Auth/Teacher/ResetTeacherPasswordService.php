<?php

namespace App\Services\Auth\Teacher;

use App\Jobs\AuthenticationJobs\SendOTPViaEmailJob;
use App\Models\OTP;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Models\Teacher;
use App\Services\ApiResponseService;
use App\Models\PasswordResetToken;
use App\Exceptions\AppException;

class ResetTeacherPasswordService
{
    public function resetPassword($passwordResetData)
    {
        $teacherExists = Teacher::where('email', $passwordResetData["email"])->first();

        if (!$teacherExists) {
            throw new AppException(
                "Teacher with email '{$passwordResetData['email']}' not found in the system.",
                404,
                "Account Not Found 🧐",
                "We couldn't find an account associated with the email address you provided. Please check the email for typos and try again.",
                null
            );
        }

        $otp = Str::random(6);

        $otp_header = Str::random(24);

        $expiresAt = Carbon::now()->addMinutes(5);

        try {
            OTP::create([
                'token_header' => $otp_header,
                'actorable_id' => $teacherExists->id,
                'actorable_type' => Teacher::class,
                'otp' => $otp,
                'expires_at' => $expiresAt,
            ]);

            SendOTPViaEmailJob::dispatch($passwordResetData['email'], $otp);

            return ['otp_header' => $otp_header];
        } catch (\Exception $e) {
            throw new AppException(
                "Failed to create OTP record for teacher ID '{$teacherExists->id}'. Error: " . $e->getMessage(),
                500,
                "Password Reset Failed 🛑",
                "A system error occurred while trying to initiate the password reset. Please try again or contact support.",
                null
            );
        }
    }

    public function verifyOtp($otp, $tokenHeader)
    {
        $otpRecord = OTP::where('otp', $otp)
            ->where('token_header', $tokenHeader)
            ->first();

        if (!$otpRecord) {
            throw new AppException(
                "OTP record not found for the provided OTP and token header.",
                400,
                "Invalid Verification Code ❌",
                "The verification code (OTP) or the session token is incorrect. Please double-check the code you entered or start the password reset process again.",
                null
            );
        }

        if ($otpRecord->isExpired()) {
            $otpRecord->delete();
            throw new AppException(
                "OTP record expired for token header '{$tokenHeader}'.",
                400,
                "Verification Code Expired ⏱️",
                "Your verification code has expired. Please request a new password reset code and enter it quickly.",
                null
            );
        }

        try {
            $otpRecord->update(['used' => true]);

            $password_reset_token = Str::random(35);

            PasswordResetToken::create([
                'token' => $password_reset_token,
                'actorable_id' => $otpRecord->actorable_id,
                'actorable_type' => Teacher::class,
                'expires_at' => Carbon::now()->addDay(),
            ]);

            $otpRecord->delete();

            return $password_reset_token;
        } catch (\Exception $e) {
            throw new AppException(
                "Failed to create password reset token after OTP verification. Error: " . $e->getMessage(),
                500,
                "Verification Failed 🚨",
                "A system error occurred after verifying your code, preventing the creation of the final reset token. Please try again or contact support.",
                null
            );
        }
    }

    public function changeInstructorPasswordUnAuthenticated($passwordData, $passwordResetToken)
    {
        $resetTokenRecord = PasswordResetToken::where('token', $passwordResetToken)->first();

        if (!$resetTokenRecord) {
            throw new AppException(
                "Invalid or expired Password Reset Token provided.",
                400,
                "Invalid Reset Link 🔗",
                "The password reset link you used is invalid, expired, or has already been used. Please ensure you clicked the latest link or request a new password reset.",
                null
            );
        }

        $teacherExists = Teacher::where('id', $resetTokenRecord->actorable_id)->first();

        if (!$teacherExists) {
            $resetTokenRecord->delete();
            throw new AppException(
                "Teacher with ID '{$resetTokenRecord->actorable_id}' not found for the valid reset token.",
                404,
                "Account Error ⚙️",
                "We found your reset request, but the associated teacher account seems to be missing. Please contact support immediately.",
                null
            );
        }

        try {
            $teacherExists->password = Hash::make($passwordData['new_password']);
            $teacherExists->save();
            $resetTokenRecord->delete();

            return ApiResponseService::success("Password Changed Successfully", null, null, 200);
        } catch (\Exception $e) {
            throw new AppException(
                "Failed to update password for teacher ID '{$teacherExists->id}'. Error: " . $e->getMessage(),
                500,
                "Password Change Failed 🛑",
                "We ran into a system error while trying to save your new password. Please try again or contact support.",
                null
            );
        }
    }
}
