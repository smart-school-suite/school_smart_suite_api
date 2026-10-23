<?php

namespace App\Services\Auth\SchoolAdmin;

use App\Models\OTP;
use Carbon\Carbon;
use App\Models\Schooladmin;
use Illuminate\Support\Facades\Hash;
use Exception;
use App\Exceptions\AuthException;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtpMail;

class ValidateOtpService
{
    public function verifyOtp(string $tokenHeader, string $otp)
    {
        try {

            if (empty($tokenHeader) || empty($otp)) {
                throw new AuthException(
                    "Token header and OTP are required.",
                    400,
                    "Validation Error",
                    "Please provide both the token header and OTP."
                );
            }

            $candidates = OTP::where('expires_at', '>', Carbon::now())->get();

            $otpRecord = $candidates->first(function ($record) use ($tokenHeader, $otp) {
                return Hash::check($tokenHeader, $record->token_header) && Hash::check($otp, $record->otp);
            });

            if (!$otpRecord) {
                throw new AuthException(
                    "Invalid or expired OTP token.",
                    401,
                    "Verification Failed",
                    "The provided OTP or token header is incorrect or has expired."
                );
            }

            $user = Schooladmin::find($otpRecord->actorable_id);

            if (!$user) {
                $otpRecord->delete();
                throw new AuthException(
                    "User associated with OTP not found. Please try logging in again.",
                    500,
                    "User Not Found",
                    "The user account linked to this OTP could not be located."
                );
            }

            $token = $user->createToken('schoolAdminToken')->plainTextToken;

            $otpRecord->delete();

            return [
                'authToken' => $token,
            ];
        } catch (AuthException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AuthException(
                "An unexpected error occurred. Please try again later.",
                500,
                "Server Error",
                "An unexpected system error occurred during OTP verification."
            );
        }
    }

    public function requestOtp(string $otpTokenHeader)
    {
        try {
            if (empty($otpTokenHeader)) {
                throw new AuthException(
                    "OTP token header is required.",
                    400,
                    "Validation Error",
                    "Please provide the OTP token header."
                );
            }

            $candidates = OTP::where('expires_at', '>', Carbon::now())
                ->with(['actorable'])
                ->get();

            $otpRecord = $candidates->first(function ($record) use ($otpTokenHeader) {
                return Hash::check($otpTokenHeader, $record->token_header);
            });

            if (!$otpRecord) {
                throw new AuthException(
                    "Expired or invalid OTP token.",
                    400,
                    "Request Failed",
                    "The provided OTP token header is invalid or has expired."
                );
            }

            $user = $otpRecord->actorable;

            if (!$user) {
                throw new AuthException(
                    "User associated with this OTP token was not found.",
                    404,
                    "User Not Found",
                    "The account linked to this OTP request could not be located."
                );
            }

            $newOtp = (string) random_int(100000, 999999);
            $expiresInMinutes = (int) config('auth.otp_expiry_minutes', 5);
            $expiresAt = Carbon::now()->addMinutes($expiresInMinutes);

            $otpRecord->update([
                'otp'        => Hash::make($newOtp),
                'expires_at' => $expiresAt,
            ]);

            Mail::to($user->email)->queue(
                new SendOtpMail(
                    otp: $newOtp,
                    expiresInMinutes: $expiresInMinutes,
                    recipientName: $user->name ?? $user->first_name ?? 'School Admin'
                )
            );

            return true;
        } catch (AuthException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AuthException(
                "An unexpected error occurred while requesting a new OTP.",
                500,
                "Server Error",
                "An unexpected system error occurred while generating a new OTP."
            );
        }
    }
}
