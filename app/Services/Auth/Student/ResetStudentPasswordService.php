<?php

namespace App\Services\Auth\Student;

use App\Jobs\SendOtpJob;
use App\Models\OTP;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Models\Student;
use App\Services\ApiResponseService;
use App\Jobs\AuthenticationJobs\SendOTPViaEmailJob;
use App\Models\PasswordResetToken;
class ResetStudentPasswordService
{
    // Implement your logic here
    public function resetPassword($passwordResetData)
    {
        $studentExists = Student::where('email', $passwordResetData["email"])->first();

        if (!$studentExists) {
            return ApiResponseService::error("student Not found", null, 404);
        }

        $otp = Str::random(6);

        $otp_header = Str::random(24);

        $expiresAt = Carbon::now()->addMinutes(5);

        OTP::create([
            'token_header' => $otp_header,
            'actorable_id' =>  $studentExists->id,
            'actorable_type' => Student::class,
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

        $passwordResetToken = Str::random(35);

        PasswordResetToken::create([
            'token' => $passwordResetToken,
            'actorable_id' => $otpRecord->actorable_id,
            'actorable_type' => Student::class,
            'expires_at' => Carbon::now()->addDay(),
        ]);
        $otpRecord->delete();

        return [
            'password_reset_token' => $passwordResetToken
        ];
    }
    public function changeStudentPasswordUnAuthenticated($passwordData, $passwordResetToken)
    {

        $passwordResetToken = PasswordResetToken::where('token', $passwordResetToken)->first();

        if (!$passwordResetToken) {
            return ApiResponseService::error("Invalid Password Reset Token", null, 400);
        }

        $student = Student::where('id', $passwordResetToken->actorable_id)->first();

        if (!$student) {
            return ApiResponseService::error("Student Not Found", null, 404);
        }

        $student->password = Hash::make($passwordData['new_password']);

        $student->save();

        $passwordResetToken->delete();

        return ApiResponseService::success("Password Changed Successfully", null, null, 200);
    }
}
