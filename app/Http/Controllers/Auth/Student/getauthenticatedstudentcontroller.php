<?php

namespace App\Http\Controllers\Auth\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class getauthenticatedstudentcontroller extends Controller
{
    //
    public function get_authenticated_student(Request $request){
        $student_authenticated_data = auth()->guard('student')->user();
         return response()->json([
            'status' => 'ok',
            'message' => 'Auth student fetched succefully',
            'student_user' => $student_authenticated_data
         ], 200);
    }
}
