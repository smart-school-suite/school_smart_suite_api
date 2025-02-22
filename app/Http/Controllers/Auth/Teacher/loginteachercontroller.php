<?php

namespace App\Http\Controllers\Auth\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class LoginTeacherController extends Controller
{
    //
    public function login_teacher(Request $request){
        $request->validate([
            'email' => 'required|string',
            'password' => 'required',
        ]);

        $user = Teacher::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'status' => 'ok',
                'phone_number' => 'The provided credentials are incorrect',
                'password' => 'Password is invalid'
            ]);
        }

        $token = $user->createToken('teacherToken')->plainTextToken;

        return response()->json([
            'status' => 'ok',
            'message' => 'Logged in successfully',
            'token' => $token]);
    }
}
