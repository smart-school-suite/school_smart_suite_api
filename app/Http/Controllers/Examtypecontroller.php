<?php

namespace App\Http\Controllers;

use App\Models\Examtype;
use Illuminate\Http\Request;

class Examtypecontroller extends Controller
{
    //

    public function create_exam_type(Request $request){
        $request->validate([
           'semester_id' => 'required|string',
           'exam_name' => 'required|string',
           'program_name' => 'required|string',
        ]);

        $new_exam_type_instance = new Examtype();

        $new_exam_type_instance->semester_id = $request->semester_id;
        $new_exam_type_instance->exam_name = $request->exam_name;
        $new_exam_type_instance->program_name = $request->program_name;

        $new_exam_type_instance->save();

        return response()->json(['message' => 'exam type created succefully'], 200);
    }

    public function delete_exam_type(Request $request, $exam_id){
        $find_exam_type = Examtype::find($exam_id);
        if(!$find_exam_type){
            return response()->json(['message' => 'exam not found'], 404);
        }

        $find_exam_type->delete();

        return response()->json(['message' => 'exam deleted succefully'], 200);
    }

    public function update_exam_type(Request $request, $exam_id){
        $find_exam_type = Examtype::find($exam_id);
        if(!$find_exam_type){
            return response()->json(['message' => 'exam not found'], 404);
        }

        $exam_data = $request->all();
        $filted_exam_data = array_filter($exam_data);
        $find_exam_type->fill($filted_exam_data);
        $find_exam_type->save();

        return response()->json(['message' => 'exam updated succefully'], 200);
    }

    public function get_all_exam_type(Request $request){
        $exam_type_data = Examtype::all();
        
        return response()->json(['exam_data' => $exam_type_data], 200);
    }
    
}
