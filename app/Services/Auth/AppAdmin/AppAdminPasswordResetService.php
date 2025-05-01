<?php

namespace App\Services\Auth\AppAdmin;

use App\Jobs\SendOtpJob;
use App\Services\ApiResponseService;
use App\Models\Edumanageadmin;
use App\Models\OTP;
use App\Models\PasswordResetToken;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AppAdminPasswordResetService
{
    // Implement your logic here
    public function resetPassword($passwordResetData)
    {
        $appAdminExists = Edumanageadmin::where('email', $passwordResetData["email"])->first();

        if (!$appAdminExists) {
            return ApiResponseService::error("Parent Not Found", null, 404);
        }

        $otp = Str::random(6);

        $otp_header = Str::random(24);

        $expiresAt = Carbon::now()->addMinutes(5);

        OTP::create([
            'token_header' => $otp_header,
            'actorable_id' => $appAdminExists->id,
            'actorable_type' => Edumanageadmin::class,
            'otp' => $otp,
            'expires_at' => $expiresAt,
        ]);
        SendOtpJob::dispatch($passwordResetData['email'], $otp);
        return ['otp_header' => $otp_header];
    }

    public function verifyOtp($otp, $otpTokenHeader)
    {

        $otpRecord = OTP::where('otp', $otp)
            ->where('token_header', $otpTokenHeader)
            ->first();

        if (!$otpRecord) {
            return ApiResponseService::error("Invald OTP token", null, 400);
        }

        if ($otpRecord->isExpired()) {
            return ApiResponseService::error("Expired Otp token", null, 400);
        }

        $password_reset_token = Str::random(35);

        PasswordResetToken::create([
            'token' => $password_reset_token,
            'actorable_id' => $otpRecord->actorable_id,
            'actorable_type' => Edumanageadmin::class,
            'expires_at' => Carbon::now()->addDay(),
        ]);

        $otpRecord->delete();

        return $password_reset_token;
    }
    public function ChangeAppAdminPasswordUnAuthenticated($passwordData, $request)
    {

        $password_reset_token = $request->header('PASSWORD_RESET_TOKEN');

        $passwordResetToken = PasswordResetToken::where('token', $password_reset_token)->first();

        if (!$passwordResetToken) {
            return ApiResponseService::error("Invalid Password Reset Token", null, 400);
        }

        $appAdmin = Edumanageadmin::where('id', $passwordResetToken->actorable_id)->first();

        if (!$appAdmin) {
            return ApiResponseService::error("Parent Not Found", null, 404);
        }
        $appAdmin->password = Hash::make($passwordData['new_password']);

        $appAdmin->save();

        $passwordResetToken->delete();

        return ApiResponseService::success("Password Changed Successfully", null, null, 200);
    }
}
