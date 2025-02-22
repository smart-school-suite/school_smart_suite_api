<?php

namespace App\Http\Controllers\Auth\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\Parents;
use Illuminate\Http\Request;

class PasswordResetController extends Controller
{
    //resetpasswordController
    public function reset_password(Request $request){
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        $email = session('password_reset_email');

        $new_password = Hash::make($request->password);

        Parents::where('email', $email)->update(['password' => $new_password]);

        session()->forget('password_reset_email');

        return response()->json([
            'status' => 'ok',
            'message' => 'Password has been successfully reset'
        ], 200);

    }
}
