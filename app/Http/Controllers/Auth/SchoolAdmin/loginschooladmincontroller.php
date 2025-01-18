<?php

namespace App\Http\Controllers\Auth\SchoolAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\Schooladmin;
use App\Models\OTP;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class loginschooladmincontroller extends Controller
{
    //
    public function login_school_admin(Request $request)
{

    $request->validate([
        'email' => 'required|string',
        'password' => 'required',
    ]);


    $user = Schooladmin::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'status' => 'error',
            'message' => 'The provided credentials are incorrect.',
        ], 400);
    }


    $otp = Str::random(6);
    $otp_header = Str::random(24);


    $expiresAt = Carbon::now()->addMinutes(5);


    OTP::create([
        'token_header' => $otp_header,
        'user_id' => $user->id,
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

}
