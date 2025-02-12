<?php

namespace App\Http\Controllers;

use App\Models\Exams;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Marks;
use App\Models\Reportcard;
use App\Models\Student;
use App\Models\Grades;

class Reportcardgenerationcontroller extends Controller
{
    //
    public function generate_student_report_card(Request $request)
    {
        $student_id = $request->route('student_id');
        $exam_id = $request->route('exam_id');
        $currentSchool = $request->attributes->get('currentSchool');
        $student = Student::Where('school_branch_id', $currentSchool->id)
            ->with(['specialty', 'department', 'level'])->findOrFail($student_id);

        $exam_marks_data = Marks::where('school_branch_id', $currentSchool->id)
            ->where('exam_id', $exam_id)
            ->where('level_id', $student->level_id)
            ->where('student_id', $student->id)
            ->where('specialty_id', $student->specialty_id)
            ->with(['course', 'exams.examtype'])->get();

        return $this->generateReportData($exam_marks_data, $student, $currentSchool, $exam_id);
    }
    private function generateReportData($marks, $student, $currentSchool, $exam_id)
    {
        $grades_data = Grades::where('school_branch_id', $currentSchool->id)
            ->where('exam_id', $exam_id)
            ->with(['exam.examtype', 'lettergrade'])
            ->get();
        $gradesMap = [];
        // Mapping grades to grade points
        foreach ($grades_data as $grade) {
            // Ensure 'lettergrade' exists and is accessible
            $letterGradeName = $grade->lettergrade ? $grade->lettergrade->letter_grade : null;

            $gradesMap[] = [
                'grade_points' => (float) $grade->grade_points,
                'minimum_score' => (float) $grade->minimum_score,
                'letter_grade_name' => $letterGradeName  // Include letter grade name
            ];
        }

        $totalScore = 0;
        $totalCredits = 0;
        $totalGradePoints = 0;
        $reportCard = [];

        // Iterate through each mark
        foreach ($marks as $mark) {
            // Ensure the score is treated as a float
            // Ensure the score is treated as a float
            $score = (float) $mark->score;
            $courseCredit = $mark->course->credit; // Credits for the course
            $points = 0; // Initialize points

            // Loop through the gradesMap array to find the corresponding grade points
            foreach ($gradesMap as $gradeEntry) {
                if ($mark->grade === $gradeEntry['letter_grade_name']) {
                    // Get grade points from the map and multiply with course credit
                    $points = $gradeEntry['grade_points'] * $courseCredit;
                    break; // Stop looping once the grade is found
                }
            }

            // Update totals
            $totalScore += $score;
            $totalCredits += $courseCredit;
            $totalGradePoints += $points;

            // Collect report card data
            $reportCard[] = [
                'course' => $mark->course->course_title,
                'credit' => $courseCredit,
                'grade' => $mark->grade,
                'score' => $score,
                // Include calculated grade points
            ];
        }



        // GPA Calculation
        $gpa = $totalCredits > 0 ? ($totalGradePoints / $totalCredits) : 0;

        // Check if marks are not empty before accessing its properties
        $examName = $marks->isNotEmpty() ? $marks->first()->exams->examtype->exam_name : null;

        // Prepare final student details
        $studentDetails = [
            'student_records' => $reportCard,
            'student_name' => $student->name,
            'level' => $student->level->level,
            'level_name' => $student->level->name,
            'exam_name' => $examName,
            'department' => $student->department->department_name,
            'specialty' => $student->specialty->specialty_name,
            'gpa' => round($gpa, 2),
            'total_score' => $totalScore
        ];


    }


}
