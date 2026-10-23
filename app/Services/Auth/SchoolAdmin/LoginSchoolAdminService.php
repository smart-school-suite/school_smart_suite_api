<?php

namespace App\Services\Auth\SchoolAdmin;

use App\Models\Schooladmin;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\OTP;
use Exception;
use App\Exceptions\AuthException;
use App\Mail\SendOtpMail;
use Illuminate\Support\Facades\Mail;

class LoginSchoolAdminService
{
    public function loginSchoolAdmin(array $payload)
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

            $user = Schooladmin::where('email', $payload['email'])->first();

            if (!$user) {
                throw new AuthException(
                    "The email you entered is not registered.",
                    401,
                    "Authentication Failed",
                    "This email address is not in our records. Please check for typos or register an account."
                );
            }

            if (!Hash::check($payload['password'], $user->password)) {
                throw new AuthException(
                    "The password you entered is incorrect.",
                    401,
                    "Authentication Failed",
                    "The password you provided does not match our records."
                );
            }

            if ($user->status === "inactive") {
                throw new AuthException(
                    "ACCOUNT_DEACTIVATED",
                    403,
                    "Account Deactivated",
                    "Your student account has been deactivated. Please contact your school administration to resolve this issue.",
                    null
                );
            }


            $otp = (string) random_int(100000, 999999);
            $otpHeader = bin2hex(random_bytes(32));
            $expiresInMinutes = (int) config('auth.otp_expiry_minutes', 5);
            $expiresAt = Carbon::now()->addMinutes($expiresInMinutes);

            OTP::create([
                'token_header'   => Hash::make($otpHeader),
                'actorable_id'   => $user->id,
                'actorable_type' => Schooladmin::class,
                'otp'            => Hash::make($otp),
                'expires_at'     => $expiresAt,
            ]);

            Mail::to($user->email)->queue(
                new SendOtpMail(
                    otp: $otp,
                    expiresInMinutes: $expiresInMinutes,
                    recipientName: $user->name ?? $user->first_name ?? 'School Admin'
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
