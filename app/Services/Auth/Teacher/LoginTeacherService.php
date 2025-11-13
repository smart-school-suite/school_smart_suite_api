<?php

namespace App\Services\Auth\Teacher;

use App\Jobs\AuthenticationJobs\SendOTPViaEmailJob;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\OTP;
use Illuminate\Support\Str;
use App\Exceptions\AppException;
class LoginTeacherService
{
    // Implement your logic here
   public function loginTeacher($loginData) {

    $user = Teacher::where('email', $loginData['email'])->first();

    if (!$user || !Hash::check($loginData['password'], $user->password)) {
        throw new AppException(
            "Login failed for email '{$loginData['email']}': Invalid email or password.",
            401,
            "Incorrect Credentials 🛑",
            "The email address or password you entered is incorrect. Please double-check your credentials and try again.",
            null
        );
    }

    if (!$user->is_active) {
        throw new AppException(
            "Teacher account with email '{$loginData['email']}' is inactive.",
            403,
            "Account Inactive 💤",
            "Your account is currently inactive. Please contact your school administrator to resolve this issue.",
            null
        );
    }


    $otp = random_int(100000, 999999);
    $otp_header = Str::random(24);
    $expiresAt = Carbon::now()->addMinutes(5);

    try {
        OTP::create([
            'token_header' => $otp_header,
            'actorable_id' => $user->id,
            'actorable_type' => Teacher::class,
            'otp' => $otp,
            'expires_at' => $expiresAt,
        ]);
    } catch (\Exception $e) {
        throw new AppException(
            "Failed to create OTP record during teacher login. Error: " . $e->getMessage(),
            500,
            "Login System Error 🚨",
            "A system error occurred while generating your one-time code. Please try logging in again in a moment.",
            null
        );
    }

    SendOTPViaEmailJob::dispatch($loginData['email'], $otp);
    return ['otp_token_header' => $otp_header];
}
}
