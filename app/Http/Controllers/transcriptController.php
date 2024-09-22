<?php

namespace App\Http\Controllers;

use App\Models\Reportcard;
use Illuminate\Http\Request;

class transcriptController extends Controller
{
    //

    public function generate_student_transcript(Request $request, $student_id){
          $student_id = $request->route('student_id');
          $currentSchool = $request->attributes->get('currentSchool');

          $student_transcript = Reportcard::where('school_branch_id', $currentSchool->id)
          ->where('student_id', $student_id)
          ->with(['student'])->get();

          if(!$student_transcript->isEmpty()){
            return response()->json([
                'status' => 'ok',
                'message' => 'Transcrip records is empty',
            ], 409);
          }

          return response()->json([
             'status' => 'ok',
             'message' => 'Student transcipt fetched succesfully',
             'student_transcript' => $student_transcript
          ], 200);

    }

    public function student_exam_ranking(Request $request, $level_id, $specialty_id){
        $level_id = $request->route('level_id');
        $specialty_id = $request->route('specialty_id');
        $currentSchool = $request->attributes->get('currentSchool');
        $student_records = Reportcard::where('school_branch_id', $currentSchool->id)
        ->where('level_id', $level_id)
        ->where('specialty_id', $specialty_id)
        ->orderBy('gpa', 'desc')
        ->with(['student'])
        ->get();
       if($student_records->isEmpty()){
            return response()->json([
                'status' => 'error',
                'message' => 'student records is empty'
            ], 409);
       }
        $rankedStudents = [];
        $currentRank = 1;
        $previousGPA = null;
        foreach ($student_records as $index => $record) {
            // If GPA is the same as the previous one, assign the same rank
            if ($record->gpa == $previousGPA) {
                $rankedStudents[] = [
                    'student_id' => $record->student_id,
                    'gpa' => $record->gpa,
                    'rank' => $currentRank,
                ];
            } else {
                // Update the rank for the current student
                $currentRank = $index + 1; // 1-based index
                $rankedStudents[] = [
                    'student_id' => $record->student_id,
                    'student_name' => $record->student->name,
                    'gpa' => $record->gpa,
                    'rank' => $currentRank,
                ];
            }
            // Store the previous GPA to check next iteration
            $previousGPA = $record->gpa;
        }

        return response()->json([
             'status' => 'ok',
             'message' => 'Student rankings fetch sucessfull',
             'ranked_students' => $rankedStudents,
        ], 200);

    }
}
