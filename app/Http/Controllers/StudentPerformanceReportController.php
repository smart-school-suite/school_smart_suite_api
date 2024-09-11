<?php

namespace App\Http\Controllers;

use App\Models\Examtimetable;
use App\Models\Grades;
use App\Models\Reportcard;
use App\Models\Student;
use App\Models\Timetable;
use Illuminate\Http\Request;

class StudentPerformanceReportController extends Controller
{
    //
    public function high_risk_course_tracking(Request $request, $student_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $student_data = Student::find($student_id);
        if(!$student_data){
            return response()->json(['message' => 'Student not found'], 404);
        }

        $student_ca_records = Reportcard::with(['exam.examType' => function($query) {
            $query->whereIn('exam_name', ['first_semester_ca', 'second_semester_ca']); // Adjusting the naming convention
        }])
        ->where('school_branch_id', $currentSchool->id)
        ->where('student_id', $student_data->id)
        ->where('level_id', $student_data->level_id)
        ->select('id', 'student_records') // Fetch only necessary fields
        ->get();

        $risky_courses = [];

        foreach ($student_ca_records as $record) {
            // Group the exams by their names for easy access
            $exams = $record->exam->pluck('examType.exam_name')->toArray(); // Ensure exam types are fetched for checking

            // Check if second-semester-ca exists
            if (in_array('second-semester-ca', $exams)) {
                // If it exists, skip checking first-semester-ca
                continue;
            }

            // If we reach here, proceed to check for first-semester-ca
            if (in_array('first-semester-ca', $exams)) {
                // Decode the JSON attribute (assuming it's named 'student_records')
                $scores = json_decode($record->student_records, true); // Decode to an associative array

                // Check scores for at-risk courses
                foreach ($scores as $course_name => $score) {
                    if ($score < 30) {
                        // Create the response with course_name and corresponding warning
                        $risky_courses[] = [
                            'course_name' => $course_name,
                            'status' => 'high risk of failing'
                        ];
                    }
                }
            }
        }

        return response()->json([
            'high_risk_courses' => array_values(array_unique($risky_courses)),
            'message' => count($risky_courses) > 0 ? 'Found high-risk courses' : 'No high-risk courses found',
        ]);
    }


    public function calculate_desired_gpa(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $student_id = $request->route('student_id');
          $request->validate([
             'gpa' => 'required',
             'exam_type_id' => 'required|string'
          ]);

          $desiredGpa = $request->input('gpa');
        $find_student_id = Student::where('school_branch_id', $currentSchool->id)->find($student_id);

        if($find_student_id){
            return response()->json(['message' => 'student not found'], 404);
        }
         
        $maxGradePoints = Grades::where('school_branch_id', $currentSchool->id)
        ->max('grade_points');

        if ($desiredGpa > $maxGradePoints || $desiredGpa <= 0) {
            return response()->json(['message' => "Desired GPA exceeds maximum allowable GPA of $maxGradePoints."], 400);
        }

        $get_exam_timetable_data = Examtimetable::where('school_branch_id', $currentSchool->id)
        ->where('specialty_id', $find_student_id->specialty_id)
        ->with(['course', 'exam.examtype' => function ($query) use ($request) {
            $query->where('id', $request->exam_type_id); // Keeps it as a single string
        }, 'exam' => function ($query) use ($find_student_id) {
            $query->where('level_id', $find_student_id->level_id);
        }])
        ->get();

        $totalCredits = $get_exam_timetable_data->sum(function ($exam) {
            return $exam->course->credits; // Assuming there's a `credits` field in the course relation
        });

        // Total points required to achieve the desired GPA
        $desiredTotalPoints = $desiredGpa * $totalCredits;

        // Array to hold required scores for each course
        $scoresNeeded = [];

        foreach ($get_exam_timetable_data as $exam) {
            $course = $exam->course;

            // Check if the course exists and has credits
            if ($course && $course->credits > 0) {
                // Use the weighted_mark as the maximum score for that exam
                $maxScore = $exam->exam->weighted_mark; // The weighted_mark field for the exam
                
                // Calculate the score needed for each course based on the weighted mark
                $neededScore = ($desiredTotalPoints / $totalCredits) * $maxScore;

                // Ensure that the needed score does not exceed the maximum weighted mark
                if ($neededScore > $maxScore) {
                    $neededScore = $maxScore; // Cap it at maxScore
                }

                // Add the course name and required score to the response array
                $scoresNeeded[] = [
                    'course_name' => $course->name,
                    'required_score' => round($neededScore, 2) // Round to 2 decimal points
                ];
            }
        }

        return response()->json([
            'required_scores' => $scoresNeeded,
        ], 200);
    }



}
