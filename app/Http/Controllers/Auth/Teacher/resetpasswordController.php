<?php

namespace App\Http\Controllers\Auth\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\Teacher;
use Illuminate\Http\Request;

class resetpasswordController extends Controller
{
    public function reset_password(Request $request){
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);
    
        // Retrieve email from the session
        $email = session('password_reset_email');
         
        $new_password = Hash::make($request->password);

        Teacher::where('email', $email)->update(['password' => $new_password]);
        
        session()->forget('password_reset_email');

        return response()->json([
            'status' => 'ok',
            'message' => 'Password has been successfully reset'
        ], 200);
        
    }
}
