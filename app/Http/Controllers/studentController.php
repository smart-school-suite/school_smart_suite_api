<?php

namespace App\Http\Controllers;

use App\Events\Examresultsreleased;
use App\Models\Exams;
use App\Models\Grades;
use Illuminate\Http\Request;
use App\Models\Marks;
use App\Models\Reportcard;
use App\Models\Student;

class studentController extends Controller
{
    //

    public function get_all_students_in_school(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $students = Student::where('school_branch_id', $currentSchool->id)->with('parents')->get();
        return response()->json(['students' => $students], 200);
    }

    public function delete_Student_Scoped(Request $request, $student_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $student_data_scoped = Student::where('school_branch_id', $currentSchool->id)
            ->find($student_id);
        if (!$student_data_scoped) {
            return response()->json(['message' => 'student not found'], 409);
        }

        $student_data_scoped->delete();

        return response()->json(['message' => 'Student deleted succesfully'], 201);
    }

    public function update_student_scoped(Request $request, $student_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $student_data_scoped = Student::where('school_branch_id', $currentSchool->id)
            ->find($student_id);
        if (!$student_data_scoped) {
            return response()->json(['message' => 'student not found'], 409);
        }

        $student_data = $request->all();
        $student_data = array_filter($student_data);
        $student_data_scoped->fill();

        return response()->json(['message' => 'Student data updated succesfully'], 200);
    }

    public function get_student_with_all_relations(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $student_data_scoped = Student::where('school_branch_id', $currentSchool->id)
            ->with('parents', 'specialty')->get();
        return response()->json(['student_data' => $student_data_scoped], 201);
    }


    public function generate_student_report_card(Request $request, $student_id, $level_id, $exam_id)
    {
        $student_id = $request->route('student_id');
        $level_id = $request->route('level_id');
        $exam_id = $request->route('exam_id');
        $currentSchool = $request->attributes->get('currentSchool');
        $student = Student::Where('school_branch_id', $currentSchool->id)
            ->with(['specialty', 'department', 'level'])->findOrFail($student_id);

        $marks = Marks::where('school_branch_id', $currentSchool->id)
            ->with(['course', 'exams'])
            ->where('student_id', $student_id)
            ->where('level_id', $level_id)
            ->where('exam_id', $exam_id)
            ->get();
        if ($marks->isEmpty()) {
            return response()->json(['message' => 'No marks found for this student.'], 404);
        }

        $reportData = $this->generateReportData($marks, $student, $currentSchool, $exam_id);

        return response()->json($reportData, 200);
    }
    private function generateReportData($marks, $student, $currentSchool, $exam_id)
    {
        // Fetching grades data for the respective school and exam
        $grades_data = Grades::where('school_branch_id', $currentSchool->id)
            ->where('exam_id', $exam_id)
            ->with(['exam.examtype', 'lettergrade'])
            ->get();
    
        $gradesMap = [];
        foreach ($grades_data as $grade) {
            $gradesMap[] = [
                'letter_grade' => $grade->lettergrade->letter_grade,
                'grade_points' => (float)$grade->grade_points,  // Ensure this is a float
                'minimum_score' => (float)$grade->minimum_score   // Ensure this is a float
            ];
        }
    
        $totalScore = 0;
        $totalCredits = 0;
        $totalGradePoints = 0;
        $reportCard = [];
    
        foreach ($marks as $mark) {
            $courseCredit = $mark->course->credit;
            $score = (float) $mark->score;  // Make sure the score is a float
            $determinedGrade = null;
            $points = 0; // Initialize points
    
            foreach ($gradesMap as $gradeEntry) {
                if ($score >= $gradeEntry['minimum_score']) {
                    $determinedGrade = $gradeEntry['letter_grade'];
                    $points = $gradeEntry['grade_points'] * $courseCredit;
                    break;
                }
            }
    
            if (is_null($determinedGrade)) {
                $determinedGrade = 'F'; 
                $points = 0; 
            }
    
            // Determine if the student passes or needs a resit
            $determinant = in_array($determinedGrade, ['A', 'B', 'C']) ? 'pass' : 'resit';
    
            // Update totals
            $totalScore += $score; 
            $totalCredits += $courseCredit; 
            $totalGradePoints += $points;
    
            // Corrected variable for determined grade
            $reportCard[] = [
                'course' => $mark->course->course_title,
                'credit' => $courseCredit,
                'score' => $score,
                'grade' => $determinedGrade,  // Changed `grade` to `determinedGrade`
                'determinant' => $determinant
            ];
        }
    
        // GPA Calculation
        $gpa = $totalCredits > 0 ? $totalGradePoints / $totalCredits : 0;
    
        // Check if marks is not empty before accessing its properties
        $examName = $marks->isNotEmpty() ? $marks->first()->exams->exam_name : null;
        $examId = $marks->isNotEmpty() ? $marks->first()->exams->id : null;  // Rename variable for clarity
    
        $studentDetails = [
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
            $examId,  // Use unique variable for clarity
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
                    'report_card' => $$student_records,
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
        return $existingRecords !== $newRecords;
    }
}
