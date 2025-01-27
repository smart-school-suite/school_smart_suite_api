<?php

namespace App\Http\Controllers;

use App\Models\Grades;
use App\Models\Exams;
use Illuminate\Http\Request;

class gradesController extends Controller
{
    //when creating grades for EXAM (FIRST_SEMESTER, SECOND_SEMESTER, THIRD_SEMESTER, NTH_SEMESETER)
    //You need to take into consideration the related weighted mark of the ca
    // eg if weighted mark for ca = 30 and weighted_exam_mark = 70 then means your grades of the exam will be based on
    // 30 + 70 = 100 which is 100
    public function makeGradeForExamScoped(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');

        $request->validate([
            'grades' => 'required|array',
            'grades.*.letter_grade_id' => 'required|string',
            'grades.*.minimum_score' => 'required|numeric|min:0|max:100|regex:/^\d+(\.\d{1,2})?$/',
            'grades.*.grade_points' => 'required|numeric|min:0|max:100|regex:/^\d+(\.\d{1,2})?$/',
            'grades.*.exam_id' => 'required|string',
            'grades.*.grade_status' => 'required|string',
        ]);

        $createdGrades = [];
        $errors = [];

        foreach ($request->grades as $gradeData) {
            $exists = Grades::where('school_branch_id', $currentSchool->id)
                ->where('exam_id', $gradeData['exam_id'])
                ->where('minimum_score', $gradeData['minimum_score'])
                ->where('letter_grade_id', $gradeData['letter_grade_id'])
                ->exists();

            if ($exists) {
                $errors[] = [
                    'exam_id' => $gradeData['exam_id'],
                    'minimum_score' => $gradeData['minimum_score'],
                    'message' => 'Grade already exists'
                ];
                continue;
            }

            $exam = Exams::where('school_branch_id', $currentSchool->id)
                ->where('id', $gradeData['exam_id'])
                ->first();

            if (!$exam) {
                $errors[] = [
                    'exam_id' => $gradeData['exam_id'],
                    'message' => 'Exam not found'
                ];
                continue;
            }

            if ($gradeData['minimum_score'] > $exam->weighted_mark) {
                $errors[] = [
                    'exam_id' => $gradeData['exam_id'],
                    'minimum_score' => $gradeData['minimum_score'],
                    'message' => 'Score cannot be greater than exam max score',
                    'exam_max_score' => $exam->weighted_mark
                ];
                continue; // Skip to the next grade if the score is too high
            }

            // Create a new grade instance
            $grade = new Grades();
            $grade->school_branch_id = $currentSchool->id;
            $grade->letter_grade_id = $gradeData['letter_grade_id'];
            $grade->grade_points = $gradeData['grade_points'];
            $grade->exam_id = $gradeData['exam_id'];
            $grade->grade_status = $gradeData['grade_status'];
            $grade->minimum_score = $gradeData['minimum_score'];
            $grade->save();

            // Add the created grade to the array
            $createdGrades[] = $grade;
        }

        // Prepare the response based on the operation results
        $response = [
            'status' => !empty($errors) ? 'partial_success' : 'success',
            'message' => !empty($errors) ? 'Some grades were not created due to errors' : 'All grades created successfully',
            'created_grades' => $createdGrades,
            'errors' => $errors,
        ];

        return response()->json($response, 200); // Return 200 OK for both successful and partial success
    }

    public function get_all_grades_scoped(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $grades_data = Grades::where('school_branch_id', $currentSchool->id)
            ->with(['exam.examtype.semesters', 'lettergrade'])->get();
        return response()->json(['grades_data' => $grades_data], 200);
    }

    public function update_grades_scoped(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $grades_id = $request->route('grade_id');
        $check_grades_data = Grades::where('school_branch_id', $currentSchool->id)
            ->find($grades_id);
        if (!$check_grades_data) {
            return response()->json([
                'status' => 'ok',
                'message' => 'grade data not found'
            ], 409);
        }

        $grades_data = $request->all();
        $grades_data = array_filter($grades_data);
        $check_grades_data->fill($grades_data);
        $check_grades_data->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'Grade updated succefully'
        ], 200);
    }

    public function delete_grades_scoped(Request $request, $grades_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $check_grades_data = Grades::where('school_branch_id', $currentSchool->id)
            ->find($grades_id);
        if (!$check_grades_data) {
            return response()->json([
                'status' => 'error',
                'message' => 'grade data not found'
            ], 409);
        }

        $check_grades_data->delete();

        return response()->json([
            'status' => 'ok',
            'message' => 'Grade deleted sucessfully',
            'deleted_grade' => $check_grades_data
        ], 200);
    }
}
