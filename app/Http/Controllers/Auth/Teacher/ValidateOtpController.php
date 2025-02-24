<?php

namespace App\Http\Controllers\Auth\Teacher;

use App\Http\Controllers\Controller;
use App\Models\OTP;
use App\Models\Teacher;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Http\Requests\OtpRequest;
use Illuminate\Http\Request;

class ValidateOtpController extends Controller
{
    //
    public function verify_otp(OtpRequest $request)
    {

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

        $user = Teacher::where('id', $otpRecord->actorable_id)->first();


        $token = $user->createToken('teacherToken')->plainTextToken;


        $otpRecord->update(['used' => true]);

        $otpRecord->delete();


        return response()->json([
            'status' => 'ok',
            'message' => 'OTP verified successfully',
            'token' => $token
        ]);
    }


    public function request_another_code(Request $request)
    {
        $token_header = $request->header('OTP_TOKEN_HEADER');
        $otpRecord = OTP::where('token_header', $token_header)->first();

        if (!$otpRecord) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        $newOtp = Str::random(6);
        $expiresAt = Carbon::now()->addMinutes(5);

        $otpRecord->update([
            'otp' => $newOtp,
            'expires_at' => $expiresAt,
        ]);
        return response()->json(
            [
                'message' => 'New OTP generated successfully',
                'otp' => $newOtp
            ],
            200
        );
    }
}
