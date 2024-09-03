<?php

namespace App\Http\Controllers;

use App\Models\Exams;
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
        
        $reportData = $this->generateReportData($marks, $student, $currentSchool);
        
        return response()->json($reportData, 200);
    }
    
    private function generateReportData($marks, $student, $currentSchool)
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
            
            // Update totals
            $totalScore += $score; 
            $totalCredits += $courseCredit; 
            $totalGradePoints += $points;
    
            // Add entry to report card
            $reportCard[] = [
                'course' => $mark->course->course_title,
                'credit' => $courseCredit,
                'score' => $score,
                'grade' => $grade,
                'determinant' => $determinant
            ];
        }
    
        // Calculate GPA
        $gpa = $totalCredits > 0 ? $totalGradePoints / $totalCredits : 0;
    
        // Prepare student details
        // Note that $marks is a collection, so you need to decide how to get exam details.
        // We assume using the first mark; adjust as needed.
        $examName = $marks->isNotEmpty() ? $marks->first()->exams->exam_name : null;
        
        $studentDetails = [
            'student_name' => $student->name,
            'level' => $student->level->level, 
            'level_name' => $student->level->name,
            'exam_name' => $examName,
            // Uncomment and adjust if needed. Ensure $marks has associated exams.
            //'exam_semester' => $marks->first()->exams->semester->name,
            'department' => $student->department->department_name, 
            'specialty' => $student->specialty->specialty_name, 
            'gpa' => round($gpa, 2), 
            'total_score' => $totalScore
        ];
    
       $exam_id = $marks->first()->exams->id;

       return $this->create_student_record($currentSchool, $student, $exam_id,  $gpa, $totalScore, $reportCard, $studentDetails);
    }

    //function to creaete student record in the report_cards table
    private function create_student_record($currentSchool, $student, $exam_id,  $gpa, $totalScore, $student_records, $studentDetails){
     
        $reportCard = ReportCard::where('school_branch_id', $currentSchool->id)
             ->where('student_id', $student->id)
            ->where('exam_id', $exam_id)
            ->where('level_id', $student->level->id)
            ->where('specialty_id', $student->specialty->id)
            ->where('department_id', $student->department->id)
            ->first();
         
            if($reportCard){
                
                $existingRecords = json_decode($reportCard->student_records, true);
                if ($this->recordsDiff($existingRecords, $student_records)) {
                    // Update the existing record
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
                    return response()->json(['message' => 
                    'No changes detected',
                    'report_card' => $student_records,
                    'student_details' => $studentDetails
                ], 204);
                }
            }

            else {
                // Create a new record since it doesn't exist
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
                return response()->json([
                    'message' => 'Report card created successfully',
                    'report_card' => $student_records,
                    'student_details' => $studentDetails
                ], 201);
            }
    }

    //grade to points
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

    private function recordsDiff(array $existingRecords, array $newRecords)
    {
        return $existingRecords !== $newRecords;
    }
}
