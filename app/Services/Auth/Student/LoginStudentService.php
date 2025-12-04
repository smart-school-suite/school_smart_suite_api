<?php

namespace App\Services\Auth\Student;

use App\Jobs\AuthenticationJobs\SendOTPViaEmailJob;
use App\Models\Student;
use App\Models\OTP;
use App\Exceptions\AppException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LoginStudentService
{
    public function loginStudent(array $loginData): array
    {
        $student = Student::where('email', $loginData['email'])->first();

        if (!$student || !Hash::check($loginData['password'], $student->password)) {
            throw new AppException(
                "Invalid credentials provided for student login.",
                401,
                "Incorrect Email or Password",
                "The email or password you entered is incorrect. Please check and try again.",
                request()->path()
            );
        }

        if ($student->status == "inactive") {
            throw new AppException(
                "ACCOUNT_DEACTIVATED",
                403,
                "Account Deactivated",
                "Your student account has been deactivated. Please contact your school administration to resolve this issue.",
                null
            );
        }

        if ($student->dropout_status == true) {
            throw new AppException(
                "STUDENT_DROPPED_OUT",
                403,
                "Dropped Out",
                "You have been marked as a dropout. Access to the system is restricted. Please contact administration for clarification.",
                null,
            );
        }

        $otp = random_int(100000, 999999);
        $otpHeader = Str::random(32);
        $expiresAt = Carbon::now()->addMinutes(10);

        try {
            OTP::create([
                'token_header'     => $otpHeader,
                'actorable_id'     => $student->id,
                'actorable_type'   => Student::class,
                'otp'              => $otp,
                'expires_at'       => $expiresAt,
            ]);
        } catch (\Exception $e) {

            throw new AppException(
                "Failed to generate login code.",
                500,
                "Temporary System Issue",
                "We couldn't send you the verification code right now. Please try again in a few minutes.",
                request()->path()
            );
        }

        SendOTPViaEmailJob::dispatch($student->email, $otp);

        return [
            'otp_token_header' => $otpHeader,
            'message'          => 'Verification code sent successfully.',
            'expires_in'       => 600
        ];
    }
}
