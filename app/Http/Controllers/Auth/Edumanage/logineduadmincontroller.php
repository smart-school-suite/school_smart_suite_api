<?php

namespace App\Http\Controllers\Auth\Edumanage;

use App\Http\Controllers\Controller;
use App\Models\Edumanageadmin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class logineduadmincontroller extends Controller
{
    //
    public function login_edumanage_admin(Request $request){
        $request->validate([
            'email' => 'required|string',
            'password' => 'required',
        ]);
        
        $user = Edumanageadmin::where('email', $request->email)->first();
            
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
                'password' => ['Password is invalid']
            ]);
        }
    
        $token = $user->createToken('eduadminToken')->plainTextToken;
    
        return response()->json(['message' => 'Logged in successfully', 'token' => $token]);
    }
}
