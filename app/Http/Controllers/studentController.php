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

    public function delete_Student_Scoped(Request $request, $student_id){
        $currentSchool = $request->attributes->get('currentSchool');
        if(!$student_id){
            return response()->json(['message' => 'student not found'], 409);
        }
        $student_data_scoped = Student::where('schoool_id', $currentSchool->id)
        ->Where('student_id',  $student_id)->get();

        $student_data_scoped->delete();

        return response()->json(['message' => 'Student deleted succesfully'], 201);
    }

    public function update_student_scoped(Request $request, $student_id){
        $currentSchool = $request->attributes->get('currentSchool');
        if(!$student_id){
            return response()->json(['message' => 'student not found'], 409);
        }
        $student_data_scoped = Student::where('schoool_id', $currentSchool->id)
        ->Where('student_id',  $student_id)->get();

        $student_data = $request->all();
        $student_data = array_filter($student_data);
        $student_data_scoped->fill();

        return response()->json(['message' => 'Student data updated succesfully'], 200);
    }

    public function get_student_with_all_relations(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $student_data_scoped = Student::where('school_id', $currentSchool->id)
        ->with('parents', 'specialty')->get();
        return response()->json(['student_data' => $student_data_scoped], 201);
    }

    
    
}
