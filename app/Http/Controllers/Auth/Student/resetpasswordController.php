<?php

namespace App\Http\Controllers\Auth\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    //
    public function reset_password(Request $request){
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        // Retrieve email from the session
        $email = session('password_reset_email');

        $new_password = Hash::make($request->password);

        Student::where('email', $email)->update(['password' => $new_password]);

        session()->forget('password_reset_email');

        return response()->json([
            'satus' => 'ok',
            'message' => 'Password has been successfully reset'
        ], 200);

    }
}
