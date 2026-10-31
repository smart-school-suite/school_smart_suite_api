<?php

namespace App\Services\Auth\Student;

use App\Exceptions\AuthException;
use App\Mail\SendOtpMail;
use App\Models\OTP;
use App\Models\SchoolBranchApiKey;
use App\Models\Student;
use App\Services\Auth\Student\Concerns\EnsuresStudentCanAuthenticate;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ValidateOtpService
{
    use EnsuresStudentCanAuthenticate;

    public function verifyOtp(?string $tokenHeader, ?string $otp)
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

            $candidates = OTP::where('actorable_type', Student::class)
                ->where('expires_at', '>', Carbon::now())
                ->get();

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

            $student = Student::find($otpRecord->actorable_id);

            if (!$student) {
                $otpRecord->delete();
                throw new AuthException(
                    "User associated with OTP not found. Please try logging in again.",
                    500,
                    "User Not Found",
                    "The student account linked to this OTP could not be located."
                );
            }

            // The account may have been deactivated between login and OTP verification.
            $this->ensureStudentCanAuthenticate($student);

            $apiKey = SchoolBranchApiKey::where('school_branch_id', $student->school_branch_id)->first();

            if (!$apiKey) {
                throw new AuthException(
                    "School configuration is incomplete. Please contact your school administration.",
                    500,
                    "Configuration Error",
                    "Your school's API key is missing, so we could not complete the sign in."
                );
            }

            $token = $student->createToken('studentToken')->plainTextToken;

            $otpRecord->delete();

            return [
                'authToken' => $token,
                'apiKey'    => $apiKey->api_key,
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

    public function requestOtp(?string $otpTokenHeader)
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

            $candidates = OTP::where('actorable_type', Student::class)
                ->where('expires_at', '>', Carbon::now())
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
                    "The provided OTP token header is invalid or has expired. Please sign in again."
                );
            }

            $student = $otpRecord->actorable;

            if (!$student) {
                throw new AuthException(
                    "User associated with this OTP token was not found.",
                    404,
                    "User Not Found",
                    "The account linked to this OTP request could not be located."
                );
            }

            $this->ensureStudentCanAuthenticate($student);

            $newOtp = (string) random_int(100000, 999999);
            $expiresInMinutes = (int) config('auth.otp_expiry_minutes', 5);
            $expiresAt = Carbon::now()->addMinutes($expiresInMinutes);

            $otpRecord->update([
                'otp'        => Hash::make($newOtp),
                'expires_at' => $expiresAt,
            ]);

            Mail::to($student->email)->queue(
                new SendOtpMail(
                    otp: $newOtp,
                    expiresInMinutes: $expiresInMinutes,
                    recipientName: $student->name ?? $student->first_name ?? 'Student'
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
