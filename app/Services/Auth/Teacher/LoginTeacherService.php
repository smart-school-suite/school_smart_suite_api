<?php

namespace App\Services\Auth\Teacher;

use App\Jobs\AuthenticationJobs\SendOTPViaEmailJob;
use App\Jobs\SendOtpJob;
use App\Models\Teacher;
use App\Services\ApiResponseService;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\OTP;
use Illuminate\Support\Str;
class LoginTeacherService
{
    // Implement your logic here
    public function loginTeacher($loginData) {

        $user = Teacher::where('email', $loginData['email'])->first();

        if (!$user || !Hash::check($loginData['password'], $user->password)) {
            return ApiResponseService::error("The provided Credentials Incorrect", null, 400);
        }

        $otp = Str::random(6);

        $otp_header = Str::random(24);

        $expiresAt = Carbon::now()->addMinutes(5);

        OTP::create([
            'token_header' => $otp_header,
            'actorable_id' => $user->id,
            'actorable_type' => Teacher::class,
            'otp' => $otp,
            'expires_at' => $expiresAt,
        ]);

        SendOTPViaEmailJob::dispatch($loginData['email'], $otp);
        return ['otp_token_header'=>$otp_header];
    }
}
