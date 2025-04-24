<?php

namespace App\Http\Controllers;

use App\Models\Examtimetable;
use App\Models\Grades;
use App\Models\StudentResults;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentPerformanceReportController extends Controller
{
    //
    public function high_risk_course_tracking(Request $request, $student_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $student_data = Student::find($student_id);
        if(!$student_data){
            return response()->json([
                'status' => 'error',
                'message' => 'Student not found'
            ], 404);
        }

        $student_ca_records = StudentResults::with(['exam.examType' => function($query) {
            $query->whereIn('exam_name', ['first_semester_ca', 'second_semester_ca']);
        }])
        ->where('school_branch_id', $currentSchool->id)
        ->where('student_id', $student_data->id)
        ->where('level_id', $student_data->level_id)
        ->select('id', 'student_records')
        ->get();

        $risky_courses = [];

        foreach ($student_ca_records as $record) {
            $exams = $record->exam->pluck('examType.exam_name')->toArray();
            if (in_array('second-semester-ca', $exams)) {
                continue;
            }

            if (in_array('first-semester-ca', $exams)) {
                $scores = json_decode($record->student_records, true);

                foreach ($scores as $course_name => $score) {
                    if ($score < 30) {
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
            return response()->json([
                'status' => 'error',
                'message' => 'student not found'
            ], 404);
        }

        $maxGradePoints = Grades::where('school_branch_id', $currentSchool->id)
        ->max('grade_points');

        if ($desiredGpa > $maxGradePoints || $desiredGpa <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => "Desired GPA exceeds maximum allowable GPA of $maxGradePoints."
            ], 400);
        }

        $get_exam_timetable_data = Examtimetable::where('school_branch_id', $currentSchool->id)
        ->where('specialty_id', $find_student_id->specialty_id)
        ->with(['course', 'exam.examtype' => function ($query) use ($request) {
            $query->where('id', $request->exam_type_id);
        }, 'exam' => function ($query) use ($find_student_id) {
            $query->where('level_id', $find_student_id->level_id);
        }])
        ->get();

        $totalCredits = $get_exam_timetable_data->sum(function ($exam) {
            return $exam->course->credits;
        });

        $desiredTotalPoints = $desiredGpa * $totalCredits;

        $scoresNeeded = [];

        foreach ($get_exam_timetable_data as $exam) {
            $course = $exam->course;
            if ($course && $course->credits > 0) {
                $maxScore = $exam->exam->weighted_mark;

                $neededScore = ($desiredTotalPoints / $totalCredits) * $maxScore;

                if ($neededScore > $maxScore) {
                    $neededScore = $maxScore;
                }

                $scoresNeeded[] = [
                    'course_name' => $course->name,
                    'required_score' => round($neededScore, 2)
                ];
            }
        }

        return response()->json([
            'required_scores' => $scoresNeeded,
        ], 200);
    }



}
