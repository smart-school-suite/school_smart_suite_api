<?php

namespace App\Services\Auth\SchoolAdmin;
use App\Jobs\AuthenticationJobs\SendOTPViaEmailJob;
use App\Models\Schooladmin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\OTP;
use Exception;
use App\Exceptions\AuthException; // Ensure this is imported

class LoginSchoolAdminService
{
    /**
     * Attempts to log in a school administrator and sends an OTP.
     *
     * @param array $loginData
     * @return array
     * @throws AuthException
     */
    public function loginSchoolAdmin(array $loginData)
    {
        try {
            if (empty($loginData['email']) || empty($loginData['password'])) {
                throw new AuthException("Email and password are required.", 400, "Validation Error", "Please ensure both email and password are provided.");
            }

            $user = Schooladmin::where('email', $loginData['email'])->first();

            if (!$user) {
                throw new AuthException("The email you entered is not registered.", 401, "Authentication Failed", "This email address is not in our records. Please check for typos or register an account.");
            }

            if (!Hash::check($loginData['password'], $user->password)) {
                throw new AuthException("The password you entered is incorrect.", 401, "Authentication Failed", "The password you provided does not match our records.");
            }

            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $otp_header = Str::random(200);
            $expiresAt = Carbon::now()->addMinutes(config('auth.otp_expiry_minutes', 5));

            OTP::create([
                'token_header' => $otp_header,
                'actorable_id' => $user->id,
                'actorable_type' => Schooladmin::class,
                'otp' => $otp,
                'expires_at' => $expiresAt,
            ]);

            // Dispatch job to send OTP
            SendOTPViaEmailJob::dispatch($user->email, $otp);

            return ['otp_token_header' => $otp_header];

        } catch (AuthException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AuthException("An unexpected error occurred during login. Please try again.", 500, "Server Error", "An unexpected system error occurred. We are investigating the issue.");
        }
    }
}
