<?php

namespace App\Http\Controllers\Auth\SchoolAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\Schooladmin;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class loginschooladmincontroller extends Controller
{
    //
    public function login_school_admin(Request $request){
        $request->validate([
            'email' => 'required|string',
            'password' => 'required',
        ]);
        
        $user = Schooladmin::where('email', $request->email)->first();
            
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
                'password' => ['Password is invalid']
            ]);
        }
    
        $token = $user->createToken('schooladminToken')->plainTextToken;
    
        return response()->json(['message' => 'Logged in successfully', 'token' => $token]);
    }
}
