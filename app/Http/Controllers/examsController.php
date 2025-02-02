<?php

namespace App\Http\Controllers;

use App\Models\Exams;
use App\Models\Marks;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use App\Models\LetterGrade;
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
      'school_year' => 'required|string',
      'specialty_id' => 'required|string'
    ]);

    $new_examdata_instance = new Exams();
    $new_examdata_instance->school_branch_id = $currentSchool->id;
    $new_examdata_instance->start_date = $request->start_date;
    $new_examdata_instance->end_date = $request->end_date;
    $new_examdata_instance->level_id = $request->level_id;
    $new_examdata_instance->exam_type_id = $request->exam_type_id;
    $new_examdata_instance->weighted_mark = $request->weighted_mark;
    $new_examdata_instance->semester_id = $request->semester_id;
    $new_examdata_instance->school_year = $request->school_year;
    $new_examdata_instance->specialty_id = $request->specialty_id;
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

      $result = [];
      foreach ($exam_data as $exam) {
          $result[] = [
            'id'=> $exam->id,
            'exam_name' => $exam->examtype->exam_name,
            'semester_name' => $exam->semester->name,
            'specailty_name' => $exam->specialty->specialty_name,
            'level_name' => $exam->specialty->level->name,
            'start_date' => $exam->start_date,
            'end_date' => $exam->end_date,
            'school_year' => $exam->school_year,
            'weighted_mark' => $exam->weighted_mark
          ];
      }
    return response()->json([
      'status' => 'ok',
      'message' => 'Exam data fetched sucessfully',
      'exam_data' => $result
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

  public function associateWeightedMarkWithLetterGrades(string $exam_id): JsonResponse
  {

      $exam = Exams::where("id", $exam_id)->with(["examtype"])->first();


      if (!$exam) {
          return response()->json([
              'status' => 'error',
              'message' => 'Exam not found'
          ], 404);
      }


      $weighted_mark = $exam->weighted_mark;
      $exam_name = $exam->examtype->name;
      $letterGrades = LetterGrade::all();
      $results = [];


      foreach ($letterGrades as $letterGrade) {
          $results[] = [
              'letter_grade_id' => $letterGrade->id,
              'exam_id' => $exam_id,
              'exam_name' => $exam->examtype->exam_name,
              'letter_grade' => $letterGrade->letter_grade,
              'maximum_score' => $weighted_mark
          ];
      }

      return response()->json([
          'status' => 'ok',
          'message' => 'Data fetched successfully',
          'exam_letter_grades' => $results,
      ]);
  }

  public function get_accessed_exams(Request $request){
    $currentSchool = $request->attributes->get('currentSchool');
    $student_id = $request->route("student_id");
    $find_student = Student::find($student_id);
    if(!$find_student){
         return response()->json([
             'status' => 'ok',
             "message" => "student not found"
         ], 200);
    }

    $exam_data = Exams::where("school_branch_id", $currentSchool->id)
                        ->where("specialty_id", $find_student->specialty_id)
                         ->where("level_id", $find_student->level_id)
                         ->with(["examtype"])
                          ->get();

    $results = [];
     foreach ($exam_data as $exams) {
        $results[] = [
            "id" => $exams->id,
            "exam_name" => $exams->examtype->exam_name
        ];
     }

     return response()->json([
          'status' => "ok",
          "message" => "data fetched successfuly",
          "exam_data" => $results
     ], 200);

  }
}
