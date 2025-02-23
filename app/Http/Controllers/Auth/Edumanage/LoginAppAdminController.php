<?php

namespace App\Http\Controllers\Auth\Edumanage;

use App\Http\Controllers\Controller;
use App\Models\Edumanageadmin;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\LoginAppAdminRequest;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\OTP;

class LoginAppAdminController extends Controller
{
    //logineduadmincontroller
    public function login_edumanage_admin(LoginAppAdminRequest $request)
    {

        $user = Edumanageadmin::where('email', $request->email)->first();

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
            'actorable_id' => $user->id,
            'actorable_type' => 'App\Models\Edumanageadmin',
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
