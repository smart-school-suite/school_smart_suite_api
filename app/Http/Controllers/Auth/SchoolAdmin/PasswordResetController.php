<?php

namespace App\Http\Controllers\Auth\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Models\Schooladmin;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\OTP;
use App\Models\PasswordResetToken;
use Illuminate\Http\Request;

class PasswordResetController extends Controller
{
    //

    public function reset_password(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $schoolAdminExists = Schooladmin::where('email', $request->email)->first();

        if (!$schoolAdminExists) {
            return ApiResponseService::error("Parent Not Found", null, 404);
        }

        $otp = Str::random(6);

        $otp_header = Str::random(24);

        $expiresAt = Carbon::now()->addMinutes(5);

        OTP::create([
            'token_header' => $otp_header,
            'actorable_id' =>  $schoolAdminExists->id,
            'actorable_type' => 'App\Models\Schooladmin',
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

    public function verify_otp(Request $request)
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
            'actorable_type' => 'App\Models\Schooladmin',
            'expires_at' => Carbon::now()->addDay(),
        ]);
        $otpRecord->delete();

        return response()->json([
            'status' => 'ok',
            'message' => 'OTP verified successfully',
            'password_reset_token' => $password_reset_token
        ]);
    }
    public function ChangeShoolAdminPasswordUnAuthenticated(Request $request)
    {
        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $password_reset_token = $request->header('PASSWORD_RESET_TOKEN');

        $passwordResetToken = PasswordResetToken::where('token', $password_reset_token)->first();

        if (!$passwordResetToken) {
            return ApiResponseService::error("Invalid Password Reset Token", null, 400);
        }

        $appAdmin = Schooladmin::where('id', $passwordResetToken->actorable_id)->first();

        if (!$appAdmin) {
            return ApiResponseService::error("Parent Not Found", null, 404);
        }
        $appAdmin->password = Hash::make($request->new_password);

        $appAdmin->save();

        $passwordResetToken->delete();

        return ApiResponseService::success("Password Changed Successfully", null, null, 200);
    }
}
