<?php

namespace App\Services\Auth\Student;

use App\Exceptions\AuthException;
use App\Mail\SendOtpMail;
use App\Models\OTP;
use App\Models\Student;
use App\Services\Auth\Student\Concerns\EnsuresStudentCanAuthenticate;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class LoginStudentService
{
    use EnsuresStudentCanAuthenticate;

    public function loginStudent(array $payload): array
    {
        try {
            if (empty($payload['email']) || empty($payload['password'])) {
                throw new AuthException(
                    "Email and password are required.",
                    400,
                    "Validation Error",
                    "Please ensure both email and password are provided."
                );
            }

            $student = Student::where('email', $payload['email'])->first();

            if (!$student) {
                throw new AuthException(
                    "The email you entered is not registered.",
                    401,
                    "Authentication Failed",
                    "This email address is not in our records. Please check for typos or contact your school administration."
                );
            }

            if (!Hash::check($payload['password'], $student->password)) {
                throw new AuthException(
                    "The password you entered is incorrect.",
                    401,
                    "Authentication Failed",
                    "The password you provided does not match our records."
                );
            }

            $this->ensureStudentCanAuthenticate($student);

            $otp = (string) random_int(100000, 999999);
            $otpHeader = bin2hex(random_bytes(32));
            $expiresInMinutes = (int) config('auth.otp_expiry_minutes', 5);
            $expiresAt = Carbon::now()->addMinutes($expiresInMinutes);

            // Invalidate any pending codes for this student so only the latest one works.
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

            Mail::to($student->email)->queue(
                new SendOtpMail(
                    otp: $otp,
                    expiresInMinutes: $expiresInMinutes,
                    recipientName: $student->name ?? $student->first_name ?? 'Student'
                )
            );

            return ['otp_token_header' => $otpHeader];
        } catch (AuthException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AuthException("An unexpected error occurred during login. Please try again.", 500, "Server Error", "An unexpected system error occurred. We are investigating the issue.");
        }
    }
}
