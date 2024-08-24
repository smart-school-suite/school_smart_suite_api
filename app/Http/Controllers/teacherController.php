<?php

namespace App\Http\Controllers;
use App\Models\Teacher;
use Illuminate\Http\Request;

class teacherController extends Controller
{
    //

    public function get_all_teachers_Without_relations(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $teachers = Teacher::Where('school_id', $currentSchool->id)->get();
        return response()->json(['teachers' => $teachers], 200);
    }

    public function get_all_teachers_with_relations_scoped(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $teachers = Teacher::where('school_id', $currentSchool->id)
        ->with('courses', 'instructoravailability');
          
        return response()->json(['teacher_data' => $teachers], 201);
    }

    public function delete_teacher_scoped(Request $request, $teacher_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $teacher_data = Teacher::Where('school_id', $currentSchool->id)->find($teacher_id);
        if(!$teacher_id){
            return response()->json(['message' => 'Teacher deleted succefully'], 409);
        }
        
        $teacher_data->delete();

        return response()->json(['message' => 'Teacher deleted sucessfully'], 200);
    }

    public function update_teacher_data_scoped(Request $request, $teacher_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $teacher_data = Teacher::Where('school_id', $currentSchool->id)->find($teacher_id);
        if(!$teacher_data){
            return response()->json(['message' => 'Teacher deleted succefully'], 409);
        }
        
        $teacher_data_request = $request->all();
        $teacher_data_request = array_filter($teacher_data_request);
        $teacher_data->fill();

        return response()->json(['message' => 'Teacher updated succefully'], 201);
    }
    public function get_all_teachers_not_scoped(Request $request){
          $teacher_data = teacher::all();
          return response()->json(['teacher_data' => $teacher_data ], 201);
    }

    


}
