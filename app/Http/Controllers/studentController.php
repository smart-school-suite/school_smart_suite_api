<?php

namespace App\Http\Controllers;

use App\Models\Exams;
use Illuminate\Http\Request;
use App\Models\Marks;
use App\Models\Student;

class studentController extends Controller
{
    //

    public function get_all_students_in_school(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $students = Student::where('school_branch_id', $currentSchool->id)->get();
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
        $currentSchool = $request->attributes->get('currentSchool');
        $student = Student::Where('school_branch_id', $currentSchool->id)->with('specialty')->findOrFail($student_id); // Assuming specialty is a relation
        $exam_data = Exams::with('department')->findOrFail($exam_id);
        $marks = Marks::where('school_branch_id', $currentSchool->id)
            ->where('student_id', $student_id)
            ->where('level_id', $level_id)
            ->where('exam_id', $exam_id)
            ->with(['course', 'exams'])
            ->get();
        if ($marks->isEmpty()) {
            return response()->json(['message' => 'No marks found for this student.'], 404);
        }
        $reportData = $this->generateReportData($marks, $student);

        return response()->json($reportData, 200);
    }

    private function generateReportData($marks, $student)
    {
        $totalScore = 0;
        $totalCredits = 0;
        $totalGradePoints = 0;
        $reportCard = [];

        foreach ($marks as $mark) {
            $courseCredit = $mark->course->credit;
            $score = $mark->score;
            $grade = $mark->grade;
            $determinant = in_array($grade, ['A', 'B', 'C']) ? 'pass' : 'resit';
            $points = $this->gradeToPoints($grade) * $courseCredit;
            $totalScore += $score;
            $totalCredits += $courseCredit;
            $totalGradePoints += $points;
            $reportCard[] = [
                'course' => $mark->course->title,
                'credit' => $courseCredit,
                'score' => $score,
                'grade' => $grade,
                'determinant' => $determinant
            ];
        }

        $gpa = $totalCredits > 0 ? $totalGradePoints / $totalCredits : 0;

        $studentDetails = [
            'student_name' => $student->name,
            'level' => $student->level->level, 
            'leve_name' => $student->level->name,
            'exam_name' => $marks->exams->exam_name,
            'exam_semester' => $marks->exams->semester->name,
            'department' => $student->department, 
            'specialty' => $student->specialty->name, 
            'gpa' => round($gpa, 2), 
            'total_score' => $totalScore
        ];

        return [
            'report_card' => $reportCard,
            'student_details' => $studentDetails
        ];
    }

    private function gradeToPoints($grade)
    {
        switch ($grade) {
            case 'A':
                return 4.0; 
            case 'B':
                return 3.0; 
            case 'C':
                return 2.0; 
            case 'D':
                return 1.0; 
            case 'E':
            case 'F':
                return 0.0; 
            default:
                return 0.0; 
        }
    }
}
