<?php

namespace App\Http\Controllers\Auth\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Models\OTP;
use App\Models\Schooladmin;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\Request;

class validateOtpController extends Controller
{
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
            return response()->json(['message' => 'Invalid OTP',
        ], 400);
        }


        if ($otpRecord->isExpired()) {
            return response()->json(['message' => 'Expired OTP'], 400);
        }

        $user = Schooladmin::where('id', $otpRecord->user_id)->first();


        $token = $user->createToken('schoolAdminToken')->plainTextToken;


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
        return response()->json([
            'message' => 'New OTP generated successfully',
            'otp' => $newOtp],
             200);
    }

}
