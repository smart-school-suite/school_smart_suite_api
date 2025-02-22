<?php

namespace App\Http\Controllers\Auth\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LogoutStudentController extends Controller
{
    //
    public function logout_student(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->noContent();
    }
}
