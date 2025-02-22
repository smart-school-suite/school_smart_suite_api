<?php

namespace App\Http\Controllers\Auth\Edumanage;

use App\Http\Controllers\Controller;
use App\Models\Edumanageadmin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class LoginAppAdminController extends Controller
{
    //logineduadmincontroller
    public function login_edumanage_admin(Request $request){
        $request->validate([
            'email' => 'required|string',
            'password' => 'required',
        ]);

        $user = Edumanageadmin::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials are incorrect',
                'password' => 'Password is invalid'
            ]);
        }
        if ($user && Hash::check($request->password, $user->password)) {
            // Generate OTP
            $otp = rand(100000, 999999);
            $user->otp = $otp;
            $user->otp_expires_at = now()->addMinutes(5);
            $user->save();

            // Send OTP via notification
            // $user->notify(new OtpNotification($otp));
            Session::put('auth_user_id', $user->id);
            return response()->json([
                'status' => 'ok',
                'message' => 'OTP sent to your registered email.',
                'otp_code' => $otp
            ], 200);
        }


    }

    public function verify_otp(Request $request){
        $request->validate([
            'otp' => 'required|digits:6',
        ]);
        $userId = Session::get('auth_user_id');
        $user = Edumanageadmin::find($userId);

        if ($user && $user->otp === $request->otp && now()->isBefore($user->otp_expires_at)) {
            // OTP is valid
            $user->otp = null; // Clear OTP after successful verification
            $user->otp_expires_at = null;
            $user->save();

            $token = $user->createToken('eduadminToken')->plainTextToken;

            // Clear the session user ID since it's no longer needed
            Session::forget('auth_user_id');

            return response()->json([
                'status' => 'ok',
                'message' => 'Logged in successfully',
               'token' => $token
           ]);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Invalid or expired OTP.'
        ], 401);
    }
}
