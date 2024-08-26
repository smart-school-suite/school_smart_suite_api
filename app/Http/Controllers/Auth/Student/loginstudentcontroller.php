<?php

namespace App\Http\Controllers\Auth\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class loginstudentcontroller extends Controller
{
    //
    public function login_student(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $request->validate([
            'email' => 'required|string',
            'password' => 'required',
        ]);
        
        $user = Student::Where('school_branch_id', $currentSchool->id)->where('email', $request->email)->first();
            
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'phone_number' => ['The provided credentials are incorrect.'],
                'password' => ['Password is invalid']
            ]);
        }
    
        $token = $user->createToken('studentToken')->plainTextToken;
    
        return response()->json(['message' => 'Logged in successfully', 'token' => $token]);
    }
}
