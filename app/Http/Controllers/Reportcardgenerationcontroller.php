<?php

namespace App\Http\Controllers;

use App\Models\Exams;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Marks;
use App\Models\Reportcard;
use App\Models\Student;
use App\Models\Grades;
use Exception;

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

        $exam_configuration = $this->exam_config($exam_id, $currentSchool);
        if (empty($exam_configuration['related_ca'])) {
            return $this->ca_marks_data_channel(
                $exam_configuration['exam']->id,
                $currentSchool,
                $student
            );
        } else {
            return $this->exam_marks_data_channel(
                $exam_configuration['exam']->id,
                $exam_configuration['related_ca']->id,
                $student,
                $currentSchool
            );
        }
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
        $examId = $marks->isNotEmpty() ? $marks->first()->exams->id : null;

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


        // Use the identified exam id
        return $this->create_student_record(
            $currentSchool,
            $student,
            $exam_id,  // Use unique variable for clarity
            $gpa,
            $totalScore,
            $reportCard,
            $studentDetails
        );
    }

    private function create_student_record(
        $currentSchool,
        $student,
        $exam_id,
        $gpa,
        $totalScore,
        $student_records,
        $studentDetails
    ) {

        $reportCard = ReportCard::where('school_branch_id', $currentSchool->id)
            ->where('student_id', $student->id)
            ->where('exam_id', $exam_id)
            ->where('level_id', $student->level->id)
            ->where('specialty_id', $student->specialty->id)
            ->where('department_id', $student->department->id)
            ->first();

        if ($reportCard) {
            $existingRecords = json_decode($reportCard->student_records, true);

            if ($this->recordsDiff($existingRecords, $student_records)) {
                $reportCard->update([
                    'school_branch_id' => $currentSchool->id,
                    'specialty_id' => $student->specialty->id,
                    'gpa' => $gpa,
                    'student_id' => $student->id,
                    'total_score' =>  $totalScore,
                    'department_id' => $student->department->id,
                    'level_id' => $student->level->id,
                    'exam_id' => $exam_id,
                    'student_records' => json_encode($student_records),
                ]);
                
                return response()->json([
                    'message' => 'Report card updated successfully',
                    'report_card' => $student_records,
                    'student_details' => $studentDetails
                ], 200);
            } else {
                return response()->json([
                    'message' =>
                    'No changes detected',
                    'report_card' => $student_records,
                    'student_details' => $studentDetails
                ], 204);
            }
        } else {
            ReportCard::create([
                'school_branch_id' => $currentSchool->id,
                'student_id' => $student->id,
                'exam_id' => $exam_id,
                'specialty_id' => $student->specialty->id,
                'gpa' => $gpa,
                'total_score' => $totalScore,
                'department_id' => $student->department->id,
                'level_id' => $student->level->id,
                'student_records' => json_encode($student_records),
            ]);


            // event(new Examresultsreleased($student->id, $exam_id, $currentSchool));
            return response()->json([
                'message' => 'Report card created successfully',
                'report_card' => $student_records,
                'student_details' => $studentDetails
            ], 201);
        }
    }

    private function recordsDiff(array $existingRecords, array $newRecords)
    {
        return $this->compareGradeArrays($existingRecords, $newRecords);
    }

    function compareGradeArrays(array $array_one, array $array_two): bool
    {
        // Check if both are arrays
        if (!is_array($array_one) || !is_array($array_two)) {
            return false;
        }

        // Check if both arrays have the same length
        if (count($array_one) !== count($array_two)) {
            return false;
        }

        return true; // All checks passed
    }

    private function exam_config($exam_id, $currentSchool)
    {
        $exam = Exams::with('examtype')->where('school_branch_id', $currentSchool->id)->find($exam_id);
        if (!$exam) {
            return null;
        }
        $programName = $exam->examtype->program_name;
        $related_exams = [
            'first_semester_exam' => 'first_semester_ca',
            'second_semester_exam' => 'second_semester_ca',
            'third_semester_exam' => 'third_semester_ca',
            'fourth_semester_exam' => 'fourth_semester_ca',
            'fifth_semester_exam' => 'fifth_semester_ca'
        ];
        if (strpos($programName, '_ca') !== false) { // If it contains '_ca' (for CAs)
            return ['exam' => $exam->load('examtype'), 'related_ca' => null]; // Return exam data with exam type as-is.
        } elseif (array_key_exists($programName, $related_exams)) {
            // If it maps to an exam type, get related CA.
            $related_ca_program_name = $related_exams[$programName];

            // Fetch the exam type for the related CA program name.
            $relatedExamType = \App\Models\Examtype::where('program_name', $related_ca_program_name)->first();

            if ($relatedExamType) {
                // Now retrieve the CA exam based on the related exam type ID.
                $caExam = \App\Models\Exams::with('examtype')->where('exam_type_id', $relatedExamType->id)->first();

                // Return both exam and related CA exam details.
                return [
                    'exam' => $exam->load('examtype'),
                    'related_ca' => $caExam ? $caExam->load('examtype') : null,
                ];
            }
        }
        return [
            'exam' => $exam->load('examtype'),
            'related_ca' => null
        ];
    }

    private function ca_marks_data_channel($ca_exam_id, $currentSchool, $student)
    {
        $ca_marks_data = Marks::where('school_branch_id', $currentSchool->id)
            ->where('exam_id', $ca_exam_id)
            ->where('level_id', $student->level_id)
            ->where('student_id', $student->id)
            ->where('specialty_id', $student->specialty_id)
            ->with(['course', 'exams.examtype'])->get();
        return $this->generateReportData($ca_marks_data, $student, $currentSchool, $ca_exam_id);
    }
    private function exam_marks_data_channel($exam_id, $ca_exam_id, $student, $currentSchool)
    {
        $aggregated_scores = Marks::where('school_branch_id', $currentSchool->id)
            ->whereIn('exam_id', [$exam_id, $ca_exam_id])
            ->where('level_id', $student->level_id)
            ->where('student_id', $student->id)
            ->where('specialty_id', $student->specialty_id)
            ->groupBy('courses_id')
            ->select('courses_id', DB::raw('SUM(score) as total_score'))
            ->get()
            ->keyBy('courses_id');

        $response = [];
        $marks = Marks::where('school_branch_id', $currentSchool->id)
            ->where('student_id', $student->id)
            ->where('level_id', $student->level_id)
            ->where('specialty_id', $student->specialty_id)
            ->with(['course', 'exams.examtype'])
            ->get();

        foreach ($marks as $mark_data) {
            $course_id = $mark_data->courses_id;
            $total_score = $aggregated_scores->has($course_id) ? $aggregated_scores->get($course_id)->total_score : 0; // Get the total score or default to 0

            $response[] = [
                'id' => $mark_data->id,
                'school_branch_id' => $mark_data->school_branch_id,
                'score' => $total_score,
                'grade' => $mark_data->grade,
                'created_at' => $mark_data->created_at,
                'updated_at' => $mark_data->updated_at,
                'student_id' => $mark_data->student_id,
                'courses_id' => $mark_data->courses_id,
                'exam_id' => $mark_data->exam_id,
                'level_id' => $mark_data->level_id,
                'specialty_id' => $mark_data->specialty_id,
                'student_batch_id' => $mark_data->student_batch_id,
                'course' => [
                    'id' => $mark_data->course->id,
                    'school_branch_id' => $mark_data->course->school_branch_id,
                    'course_code' => $mark_data->course->course_code,
                    'course_title' => $mark_data->course->course_title,
                    'credit' => $mark_data->course->credit,
                    'created_at' => $mark_data->course->created_at,
                    'updated_at' => $mark_data->course->updated_at,
                    'specialty_id' => $mark_data->course->specialty_id,
                    'department_id' => $mark_data->course->department_id,
                    'level_id' => $mark_data->course->level_id,
                    'semester_id' => $mark_data->course->semester_id,
                ],
                'exams' => [
                    'id' => $mark_data->exam_id,
                    'school_branch_id' => $mark_data->school_branch_id,
                    'start_date' => $mark_data->exams->start_date,
                    'end_date' => $mark_data->exams->end_date,
                    'weighted_mark' => $mark_data->exams->weighted_mark,
                    'created_at' => $mark_data->exams->created_at,
                    'updated_at' => $mark_data->exams->updated_at,
                    'exam_type_id' => $mark_data->exams->exam_type_id,
                    'level_id' => $mark_data->exams->level_id,
                    'semester_id' => $mark_data->exams->semester_id,
                    'examtype' => [
                        'id' => $mark_data->exams->examtype->id,
                        'exam_name' => $mark_data->exams->examtype->exam_name,
                        'program_name' => $mark_data->exams->examtype->program_name,
                        'created_at' => $mark_data->exams->examtype->created_at,
                        'updated_at' => $mark_data->exams->examtype->updated_at,
                        'semester_id' => $mark_data->exams->examtype->semester_id,
                    ]
                ]
            ];
        }

        $filtered_array = array_filter($response, function ($item) use ($exam_id) {
            return $item['exam_id'] === $exam_id;
        });
        $filtered_array = array_values($filtered_array);

        return $filtered_array;
    }
}
