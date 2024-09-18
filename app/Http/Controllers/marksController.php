<?php

namespace App\Http\Controllers;

use App\Models\Marks;
use App\Models\Grades;
use App\Models\Exams;
use App\Models\Student;
use Illuminate\Http\Request;

class marksController extends Controller
{
    public function add_student_mark(Request $request)
    {
        $request->validate([
            'student_id' => 'required|string',
            'courses_id' => 'required|string',
            'exam_id' => 'required|string',
            'level_id' => 'required|string',
            'specialty_id' => 'required|string',
            'score' => 'required'
        ]);

        $currentSchool = $request->attributes->get('currentSchool');
        //checking if student with this mark already exist

        $find_student = Student::where('school_branch_id', $currentSchool->id)->find($request->student_id);
        if (!$find_student) {
            return response()->json([
                'status' => 'ok',
                'message' => 'student not found'
            ], 404);
        }

        $check_if_duplicate_records = Marks::Where('school_branch_id', $currentSchool->id)
            ->Where('courses_id', $request->courses_id)
            ->Where('exam_id', $request->exam_id)
            ->Where('level_id', $request->level_id)
            ->Where('specialty_id', $request->specialty_id)
            ->Where('student_id', $request->student_id)  
            ->exists();

        if ($check_if_duplicate_records) {
            return response()->json([
                'status' => 'ok',
                'message' => 'No student can have dublicated data entries',
            ], 409);
        }


        $exam = Exams::Where('school_branch_id', $currentSchool->id)
            ->findOrFail($request->exam_id);

        if ($request->score > $exam->weighted_mark) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Score cannot be greater than the exam mark.',
            ], 409);
        }

        // Determine the grade using the custom grading logic
        $grade = $this->determineGrade($request->score, $currentSchool->id, $request->exam_id);

        // Create Marks entry
        Marks::create([
            'student_id' => $request->student_id,
            'exam_id' => $exam->id,
            'score' => $request->score,
            'grade' => $grade,
            'student_batch_id' => $find_student->student_batch_id,
            'level_id' => $request->level_id,
            'courses_id' => $request->courses_id, // Assuming this is being provided in the request
            'school_branch_id' => $currentSchool->id,
            'specialty_id' => $request->specialty_id
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Marks added successfully!',
            'grade' =>   $grade
        ], 201);
    }
    private function determineGrade($score, $schoolId, $exam_id)
    {
       
        $grades = Grades::with('lettergrade') // Eager loading the lettergrade relationship
            ->where('school_branch_id', $schoolId)
            ->where('exam_id', $exam_id)
            ->orderBy('minimum_score', 'desc')
            ->get();
        foreach ($grades as $grade) {
            if ($score >= $grade->minimum_score) {
                return $grade->lettergrade->letter_grade; // Accessing the letter grade via the relationship
            }
        }

        return 'F';  // Default if no grade matches
    }
    public function delete_mark_of_student_scoped(Request $request, $mark_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $check_if_school_exist = Marks::Where('school_branch_id', $currentSchool->id)->find($mark_id);

        if (!$check_if_school_exist) {
            return response()->json(['message' => 'Student mark record not found'], 409);
        }

        $check_if_school_exist->delete();

        return response()->json(['message' => 'Student mark deleted succefully'], 200);
    }

    public function update_student_mark_scoped(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $mark_id = $request->route('mark_id');
        $check_if_school_exist = Marks::Where('school_branch_id', $currentSchool->id)->find($mark_id);

        if (!$check_if_school_exist) {
            return response()->json(['message' => 'Student mark record not found'], 409);
        }

        $student_mark_data = $request->all();
        $student_mark_data = array_filter($student_mark_data);
        $check_if_school_exist->fill($student_mark_data);

        $check_if_school_exist->save();

        return response()->json(['message' => 'Student mark updated succefully'], 200);
    }

    public function get_all_student_marks(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $exam_id = $request->route('exam_id');
        $student_id = $request->route('student_id');

        $find_student = Student::where('school_branch_id', $currentSchool->id)->find($student_id);
        $find_exam = Exams::where('school_branch_id', $currentSchool->id)->find($exam_id);

        if (!$student_id || !$find_exam || is_null($find_student)) {
            return response()->json([
                'status' => 'ok',
                'message' => 'The provided credentials are invalid'
            ]);
        }
        $scores_data = Marks::where('school_branch_id', $currentSchool->id)
            ->where('exam_id', $exam_id)
            ->where('student_id', $find_student->id)
            ->with(['student', 'course', 'exams.examtype', 'level'])
            ->get();
        return response()->json([
            'status' => 'ok',
            'message' => 'scores fetched successfully',
            'student_scores_data' => $scores_data
        ], 201);
    }
}
