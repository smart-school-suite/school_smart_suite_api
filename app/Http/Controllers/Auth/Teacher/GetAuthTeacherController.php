<?php

namespace App\Http\Controllers\Auth\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetAuthTeacherController extends Controller
{
    //GetAuthTeacherController
    public function get_authenticated_teacher(Request $request){
        $teacher_authenticated_data = auth()->guard('teacher')->user();
         return response()->json([
            'status' => 'ok',
            'message' => 'teacher authenticated teacher fetched succesfull',
            'teacher_user' => $teacher_authenticated_data
         ], 200);
    }
}
