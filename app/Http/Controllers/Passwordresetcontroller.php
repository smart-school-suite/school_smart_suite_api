<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Support\Str;
use App\Models\PasswordReset;
use Illuminate\Http\Request;

class PasswordResetcontroller extends Controller
{
    //
    public function request_password_reset_otp(Request $request){
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = $request->email;

        $check_if_user_exist = Student::where('email', $email)->exists();

        if(!$check_if_user_exist){
            return response()->json([
                'status' => 'error',
                'message' => "No user exist with this email"
            ], 409);
        }

        $otp = Str::random(6);

        $expiresAt = now()->addMinutes(10);

        session(['password_reset_email' => $email]);

        PasswordReset::updateOrCreate(
            ['email' => $email],
            ['otp' => $otp, 'created_at' => now(), 'expires_at' => $expiresAt]
        );

    // Send OTP to user via mail
 //   Mail::raw("Your OTP is: $otp", function ($message) use ($email) {
        //$message->to($email)
     //           ->subject('Your OTP for Password Reset');
   // });

    return response()->json([
        'status' => 'ok',
        'message' => 'A code has been sent to your email'
    ], 200);
    }

    public function verify_otp(Request $request){
        $request->validate([
           'otp' => 'required|string'
        ]);

        $otp = $request->otp;

        $email = session('password_reset_email');

        $passwordReset = PasswordReset::where('email', $email)
        ->where('otp', $otp)
        ->where('expires_at', '>=', now()) // Ensure OTP is not expired
        ->first();

        if (!$passwordReset) {

        return response()->json([
            'status' => 'error',
            'message' => 'Invalid or expired OTP'
        ], 400);

       }

       PasswordReset::where('email', $email)->delete();

       return response()->noContent();
    }
}
