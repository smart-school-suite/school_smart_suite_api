<?php

namespace App\Http\Controllers;

use App\Models\Exams;
use App\Models\Marks;
use Illuminate\Http\Request;

class examsController extends Controller
{
    //
   public function create_exam_scoped(Request $request){
      $currentSchool = $request->attributes->get('currentSchool');
      $request->validate([
        'exam_name' => 'required|string',
        'start_date' => 'required|date',
        'end_date' => 'required|date',
        'level_id' => 'string|required',
        'weighted_mark' => 'required|decimal',
        'semester' => 'required|string'
      ]);

      $new_examdata_instance = new Exams();
      $new_examdata_instance->school_branch_id = $currentSchool->id;
      $new_examdata_instance->exam_name = $request->exam_name;
      $new_examdata_instance->start_date = $request->start_date;
      $new_examdata_instance->end_date = $request->end_date;
      $new_examdata_instance->level_id = $request->level_id;
      $new_examdata_instance->weighted_mark = $request->weighted_mark;
      $new_examdata_instance->semester = $request->semester;

      $new_examdata_instance->save();

      return response()->json(['message' => 'exam created succesfully'], 200);

   }

   public function update_exam_scoped(Request $request, $exam_id){
      $currentSchool = $request->attributes->get('currentSchool');
      $school_data = Exams::where('school_branch_id', $currentSchool->id)
      ->find($exam_id);
      if(!$school_data){
        return response()->json(['message' => 'Exam not found'], 409);
      }

      $exam_data = $request->all();
      $exam_data = array_filter($exam_data);
      $school_data->fill($exam_data);

      return response()->json(['message' => 'Exam data updated succesfully'], 201);
   }


   public function delete_school_exam(Request $request, $exam_id){
    $currentSchool = $request->attributes->get('currentSchool');
    $school_data = Exams::where('school_branch_id', $currentSchool->id)
    ->find($exam_id);
    if(!$school_data){
      return response()->json(['message' => 'Exam not found'], 409);
    }

    $school_data->delete();

    return response()->json(['message' => 'Exam deleted successfully'], 201);
   }

   public function get_all_exams(Request $request){
    $currentSchool = $request->attributes->get('currentSchool');
    $exam_data = Exams::where('school_branch_id', $currentSchool->id)->get();
    return response()->json(['exam_data' => $exam_data], 201);
   }

   
}
