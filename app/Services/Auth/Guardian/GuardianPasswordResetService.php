<?php

namespace App\Services\Auth\Guardian;

use App\Models\OTP;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Services\ApiResponseService;
use App\Models\Parents;
use App\Models\PasswordResetToken;

class GuardianPasswordResetService
{
    // Implement your logic here

    public function resetPassword($passwordResetData)
    {
        $parentExists = Parents::where('email', $passwordResetData['email'])->first();

        if (!$parentExists) {
            return ApiResponseService::error("Parent Not Found", null, 404);
        }

        $otp = Str::random(6);

        $otp_header = Str::random(24);

        $expiresAt = Carbon::now()->addMinutes(5);

        OTP::create([
            'token_header' => $otp_header,
            'actorable_id' =>  $parentExists->id,
            'actorable_type' => 'App\Models\Parents',
            'otp' => $otp,
            'expires_at' => $expiresAt,
        ]);

        return ['otp_header' => $otp_header, 'otp' => $otp];
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

        $password_reset_token = Str::random(35);

        PasswordResetToken::create([
            'token' => $password_reset_token,
            'actorable_id' => $otpRecord->actorable_id,
            'actorable_type' => 'App\Models\Parents',
            'expires_at' => Carbon::now()->addDay(),
        ]);
        $otpRecord->delete();

        return $password_reset_token;
    }
    public function changeGuardianPasswordUnAuthenticated($passwordData, $passwordResetToken)
    {

        $passwordResetToken = PasswordResetToken::where('token', $passwordResetToken)->first();

        if (!$passwordResetToken) {
            return ApiResponseService::error("Invalid Password Reset Token", null, 400);
        }

        $parent = Parents::where('id', $passwordResetToken->actorable_id)->first();

        if (!$parent) {
            return ApiResponseService::error("Parent Not Found", null, 404);
        }
        $parent->password = Hash::make($passwordData['new_password']);

        $parent->save();

        $passwordResetToken->delete();

        return ApiResponseService::success("Password Changed Successfully", null, null, 200);
    }
}
