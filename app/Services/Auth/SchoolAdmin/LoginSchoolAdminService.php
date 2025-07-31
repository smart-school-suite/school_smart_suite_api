<?php

namespace App\Services\Auth\SchoolAdmin;
use App\Jobs\AuthenticationJobs\SendOTPViaEmailJob;
use App\Models\Schooladmin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\OTP;
use Exception;
use Illuminate\Support\Facades\Log;

class LoginSchoolAdminService
{
    // Implement your logic here
    public function loginSchoolAdmin(array $loginData)
    {
        try {
            if (empty($loginData['email']) || empty($loginData['password'])) {
                throw new Exception("Email and password are required.", 400);
            }

            $user = Schooladmin::where('email', $loginData['email'])->first();

            if (!$user || !Hash::check($loginData['password'], $user->password)) {
                throw new Exception("Invalid credentials provided.", 401);
            }

            $otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

            $otp_header = Str::random(200);

            $expiresAt = Carbon::now()->addMinutes(config('auth.otp_expiry_minutes', 5));

            OTP::create([
                'token_header' => $otp_header,
                'actorable_id' => $user->id,
                'actorable_type' => Schooladmin::class,
                'otp' => $otp,
                'expires_at' => $expiresAt,
            ]);

            SendOTPViaEmailJob::dispatch($user->email, $otp);

            return ['otp_token_header' => $otp_header];

        } catch (Exception $e) {
            Log::error("LoginSchoolAdmin failed: " . $e->getMessage(), [
                'email' => $loginData['email'] ?? 'N/A',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new Exception("An unexpected error occurred during login. Please try again.",  500);
        }
    }
}
