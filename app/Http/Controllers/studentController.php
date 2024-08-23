<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;

class studentController extends Controller
{
    //

    public function get_all_students_in_school(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $students = Student::where('school_id', $currentSchool->id)->get();
        return response()->json(['students' => $students], 200);
    }
    
}
