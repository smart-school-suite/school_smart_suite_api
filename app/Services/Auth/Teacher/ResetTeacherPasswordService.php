<?php

namespace App\Services\Auth\Teacher;

use App\Jobs\AuthenticationJobs\SendOTPViaEmailJob;
use App\Jobs\SendOtpJob;
use App\Models\OTP;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Models\Teacher;
use App\Services\ApiResponseService;
use App\Models\PasswordResetToken;
class ResetTeacherPasswordService
{
    // Implement your logic here
    public function resetPassword($passwordResetData)
    {
        $teacherExists = Teacher::where('email', $passwordResetData["email"])->first();

        if (!$teacherExists) {
            return ApiResponseService::error("Teacher  Not found", null, 404);
        }

        $otp = Str::random(6);

        $otp_header = Str::random(24);

        $expiresAt = Carbon::now()->addMinutes(5);

        OTP::create([
            'token_header' => $otp_header,
            'actorable_id' =>  $teacherExists->id,
            'actorable_type' => Teacher::class,
            'otp' => $otp,
            'expires_at' => $expiresAt,
        ]);
        SendOTPViaEmailJob::dispatch($passwordResetData['email'], $otp);
        return ['otp_header' => $otp_header];
    }
    public function verifyOtp($otp, $tokenHeader)
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
    }
    public function changeInstructorPasswordUnAuthenticated($passwordData, $passwordResetToken)
    {

        $passwordResetToken = PasswordResetToken::where('token', $passwordResetToken)->first();

        if (!$passwordResetToken) {
            return ApiResponseService::error("Invalid Password Reset Token", null, 400);
        }

        $teacherExists = Teacher::where('id', $passwordResetToken->actorable_id)->first();

        if (!$teacherExists) {
            return ApiResponseService::error("Teacher Not Found", null, 404);
        }

        $teacherExists->password = Hash::make($passwordData['new_password']);

        $teacherExists->save();

        $passwordResetToken->delete();

        return ApiResponseService::success("Password Changed Successfully", null, null, 200);
    }
}
