<?php

namespace App\Http\Controllers\Auth\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use App\Models\OTP;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LoginStudentController extends Controller
{
    //
    public function login_student(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required',
        ]);

        $studentExists = Student::where('email', $request->email)->first();

        if (!$studentExists || !Hash::check($request->password, $studentExists->password)) {
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
            'actorable_id' => $studentExists->id,
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
