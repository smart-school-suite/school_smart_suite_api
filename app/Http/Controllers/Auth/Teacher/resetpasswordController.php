<?php

namespace App\Http\Controllers\Auth\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\Teacher;
use Illuminate\Support\Str;
use App\Services\ApiResponseService;
use Carbon\Carbon;
use App\Models\OTP;
use App\Models\PasswordResetToken;
use App\Http\Requests\OtpRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\ChangePasswordUnAuthRequest;

class ResetPasswordController extends Controller
{
    public function reset_password(ResetPasswordRequest $request)
    {

        $teacherExists = Teacher::where('email', $request->email)->first();

        if (!$teacherExists) {
            return ApiResponseService::error("Parent Not Found", null, 404);
        }

        $otp = Str::random(6);

        $otp_header = Str::random(24);

        $expiresAt = Carbon::now()->addMinutes(5);

        OTP::create([
            'token_header' => $otp_header,
            'actorable_id' =>   $teacherExists->id,
            'actorable_type' => 'App\Models\Teacher',
            'otp' => $otp,
            'expires_at' => $expiresAt,
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => 'OTP sent successfully',
            'OTP' => $otp,
            'otp_token_header' => $otp_header,
        ]);
    }

    public function verify_otp(OtpRequest $request)
    {
        $request->validate([
            'otp' => 'required|string',
        ]);

        $token_header = $request->header('OTP_TOKEN_HEADER');

        $otpRecord = OTP::where('otp', $request->otp)
            ->where('token_header', $token_header)
            ->first();

        if (!$otpRecord) {
            return response()->json([
                'message' => 'Invalid OTP',
            ], 400);
        }

        if ($otpRecord->isExpired()) {
            return response()->json(['message' => 'Expired OTP'], 400);
        }

        $otpRecord->update(['used' => true]);


        $password_reset_token = Str::random(35);

        PasswordResetToken::create([
            'token' => $password_reset_token,
            'actorable_id' => $otpRecord->actorable_id,
            'actorable_type' => 'App\Models\Teacher',
            'expires_at' => Carbon::now()->addDay(),
        ]);

        $otpRecord->delete();

        return response()->json([
            'status' => 'ok',
            'message' => 'OTP verified successfully',
            'password_reset_token' => $password_reset_token
        ]);
    }
    public function ChangeTeacherPasswordUnAuthenticated(ChangePasswordUnAuthRequest $request)
    {
        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $password_reset_token = $request->header('PASSWORD_RESET_TOKEN');

        $passwordResetToken = PasswordResetToken::where('token', $password_reset_token)->first();

        if (!$passwordResetToken) {
            return ApiResponseService::error("Invalid Password Reset Token", null, 400);
        }

        $appAdmin = Teacher::where('id', $passwordResetToken->actorable_id)->first();

        if (!$appAdmin) {
            return ApiResponseService::error("Parent Not Found", null, 404);
        }
        $appAdmin->password = Hash::make($request->new_password);

        $appAdmin->save();

        $passwordResetToken->delete();

        return ApiResponseService::success("Password Changed Successfully", null, null, 200);
    }
}
