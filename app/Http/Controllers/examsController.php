<?php

namespace App\Http\Controllers;

use App\Models\Exams;
use App\Models\Marks;
use Illuminate\Http\Request;

class examsController extends Controller
{
  //
  public function create_exam_scoped(Request $request)
  {
    $currentSchool = $request->attributes->get('currentSchool');
    $request->validate([
      'start_date' => 'required',
      'end_date' => 'required',
      'exam_type_id' => 'required|string',
      'level_id' => 'string|required',
      'weighted_mark' => 'required',
      'semester_id' => 'required|string',
      'grade_points' => 'required|numeric',
      'grade_status' => 'string'
    ]);

    $new_examdata_instance = new Exams();
    $new_examdata_instance->school_branch_id = $currentSchool->id;
    $new_examdata_instance->start_date = $request->start_date;
    $new_examdata_instance->end_date = $request->end_date;
    $new_examdata_instance->level_id = $request->level_id;
    $new_examdata_instance->exam_type_id = $request->exam_type_id;
    $new_examdata_instance->weighted_mark = $request->weighted_mark;
    $new_examdata_instance->semester_id = $request->semester_id;
    $new_examdata_instance->grade_points = floatval($request->grade_points);
    $new_examdata_instance->grade_status = $request->grade_status;
    $new_examdata_instance->save();

    return response()->json([
      'status' => 'ok',
      'message' => 'exam created succesfully',
      'exam_data' => $new_examdata_instance
    ], 200);
  }

  public function update_exam_scoped(Request $request, $exam_id)
  {
    $currentSchool = $request->attributes->get('currentSchool');
    $exam_id = $request->route('exam_id');
    $school_data = Exams::where('school_branch_id', $currentSchool->id)
      ->find($exam_id);
    if (!$school_data) {
      return response()->json([
        'status' => 'ok',
        'message' => 'Exam not found'
      ], 409);
    }

    $exam_data = $request->all();
    $exam_data = array_filter($exam_data);
    $school_data->fill($exam_data);
    $school_data->save();

    return response()->json([
      'status' => 'ok',
      'message' => 'Exam data updated succesfully'
    ], 201);
  }


  public function delete_school_exam(Request $request, $exam_id)
  {
    $currentSchool = $request->attributes->get('currentSchool');
    $school_data = Exams::where('school_branch_id', $currentSchool->id)
      ->find($exam_id);
    if (!$school_data) {
      return response()->json([
        'status' => 'ok',
        'message' => 'Exam not found'
      ], 409);
    }

    $school_data->delete();

    return response()->json([
      'status' => 'ok',
      'message' => 'Exam deleted successfully',
      'deleted_exam' => $school_data
    ], 201);
  }

  public function get_all_exams(Request $request)
  {
    $currentSchool = $request->attributes->get('currentSchool');
    $exam_data = Exams::where('school_branch_id', $currentSchool->id)
      ->with(['examtype', 'semester', 'specialty.level'])
      ->get();
    if ($exam_data->isEmpty()) {
      return response()->json([
        'status' => 'ok',
        'message' => 'No exam records found',
      ], 400);
    }
    return response()->json([
      'status' => 'ok',
      'message' => 'Exam data fetched sucessfully',
      'exam_data' => $exam_data
    ], 201);
  }

  public function get_exam_details(Request $request){
    $currentSchool = $request->attributes->get('currentSchool');
    $exam_id = $request->route('exam_id');
    $find_exam = Exams::find($exam_id);
    if(!$find_exam){
       return response()->json([
         "status" => "error",
         "message" => "Exam not found"
       ], 400);
    }

    $exam_details = Exams::where("school_branch_id", $currentSchool->id)
                           ->where("id", $exam_id)
                           ->with(['specialty', 'examtype', 'semester', 'level',])
                            ->get();
      return response()->json([
         "status" => "ok",
         "message" => "Exam details fetched succefully",
         "exam_details" => $exam_details
      ], 201);
  }
}
