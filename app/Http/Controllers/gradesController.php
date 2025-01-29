<?php

namespace App\Http\Controllers;

use App\Models\Grades;
use App\Models\Exams;
use App\Models\Examtype;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        $max_grade_points = $currentSchool->max_gpa;

        $request->validate([
            'grades' => 'required|array',
            'grades.*.letter_grade_id' => 'required|string',
            'grades.*.minimum_score' => 'required|numeric|min:0|max:100|regex:/^\d+(\.\d{1,2})?$/',
            'grades.*.maximum_score' => 'required|numeric|min:0|max:100|regex:/^\d+(\.\d{1,2})?$/',
            'grades.*.determinant' => 'required|string',
            'grades.*.grade_points' => "required|numeric|min:0|max:{$max_grade_points}|regex:/^\d+(\.\d{1,2})?$/", // Use max_grade_points for validation
            'grades.*.exam_id' => 'required|string',
            'grades.*.grade_status' => 'required|string',
        ]);

        $createdGrades = [];
        $errors = [];

        foreach ($request->grades as $gradeData) {

            $exists = Grades::where('school_branch_id', $currentSchool->id)
                ->where('exam_id', $gradeData['exam_id'])
                ->where('minimum_score', $gradeData['minimum_score'])
                ->where('maximum_score', $gradeData['maximum_score'])
                ->where('letter_grade_id', $gradeData['letter_grade_id'])
                ->exists();

            if ($exists) {
                $errors[] = [
                    'exam_id' => $gradeData['exam_id'],
                    'minimum_score' => $gradeData['minimum_score'],
                    'maximum_score' => $gradeData['maximum_score'],
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


            if ($gradeData['minimum_score'] > $exam->weighted_mark || $gradeData['maximum_score'] > $exam->weighted_mark) {
                $errors[] = [
                    'exam_id' => $gradeData['exam_id'],
                    'minimum_score' => $gradeData['minimum_score'],
                    'maximum_score' => $gradeData['maximum_score'],
                    'message' => 'Scores cannot be greater than exam max score',
                    'exam_max_score' => $exam->weighted_mark
                ];
                continue;
            }


            if ($gradeData['minimum_score'] > $gradeData['maximum_score']) {
                $errors[] = [
                    'exam_id' => $gradeData['exam_id'],
                    'minimum_score' => $gradeData['minimum_score'],
                    'maximum_score' => $gradeData['maximum_score'],
                    'message' => 'Minimum score cannot be greater than maximum score'
                ];
                continue;
            }


            $grade = new Grades();
            $grade->school_branch_id = $currentSchool->id;
            $grade->letter_grade_id = $gradeData['letter_grade_id'];
            $grade->grade_points = $gradeData['grade_points'];
            $grade->exam_id = $gradeData['exam_id'];
            $grade->determinant = $gradeData['determinant'];
            $grade->grade_status = $gradeData['grade_status'];
            $grade->minimum_score = $gradeData['minimum_score'];
            $grade->maximum_score = $gradeData['maximum_score'];
            $grade->save();


            $createdGrades[] = $grade;
        }


        $response = [
            'status' => !empty($errors) ? 'partial_success' : 'success',
            'message' => !empty($errors) ? 'Some grades were not created due to errors' : 'All grades created successfully',
            'created_grades' => $createdGrades,
            'errors' => $errors,
        ];

        return response()->json($response, 200);
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

    public function findExamsBasedOnCriteria($examId)
    {

        $exam = Exams::with('examType')->find($examId);

        if (!$exam) {
            return response()->json(['message' => 'Exam not found'], 404);
        }

        $examType = $exam->examType;

        if (!$examType || $examType->type !== 'exam') {
            return response()->json(['message' => 'Exam type is not of type exam or not found'], 400);
        }

        // Step 3: Retrieve the semester directly from the exam type
        $semester = $examType->semester; // Assuming semester is a string (e.g., 'first', 'second')

        // Step 4: Attempt to find an exam type with the same semester of type 'ca'
        $caExamType = Examtype::where('semester', $semester)
            ->where('type', 'ca')
            ->first();

        if (!$caExamType) {
            return response()->json(['message' => 'Corresponding CA exam type not found'], 404);
        }

        // Step 5: Find additional exams based on the previously gathered attributes
        $additionalExams = Exams::where('school_year', $exam->school_year)
            ->where('exam_type_id', $caExamType->id) // Using the CA exam type ID
            ->where('specialty_id', $exam->specialty_id)
            ->where('level_id', $exam->level_id)
            ->where('semester_id', $exam->semester_id)
            ->where('department_id', $exam->department_id)
            ->get();

        // Step 6: Return the results or handle the case of no additional exams found
        return response()->json([
            'message' => 'Exams retrieved successfully',
            'data' => $additionalExams,
        ], 200);
    }


    public function findRelatedExamCa(string $examId)
    {
        // Step 1: Attempt to find the exam by ID
        $exam = Exams::with('examType')->find($examId);

        if (!$exam) {
            throw new ModelNotFoundException('Exam not found');
        }

        // Step 2: Get the corresponding Exam Type from the joined relation
        $examType = $exam->examType; // Eager loaded examType relationship

        if (!$examType || $examType->type !== 'exam') {
            throw new ModelNotFoundException('Exam type is not of type exam or not found');
        }

        // Step 3: Retrieve the semester directly from the exam type
        $semester = $examType->semester; // Assuming semester is a string (e.g., 'first', 'second')

        // Step 4: Attempt to find an exam type with the same semester of type 'ca'
        $caExamType = Examtype::where('semester', $semester)
                                ->where('type', 'ca')
                                ->first();

        if (!$caExamType) {
            throw new ModelNotFoundException('Corresponding CA exam type not found');
        }

        // Step 5: Find additional exams based on the gathered attributes
        $additionalExams = Exams::where('school_year', $exam->school_year)
            ->where('exam_type_id', $caExamType->id) // Using the CA exam type ID
            ->where('specialty_id', $exam->specialty_id)
            ->where('level_id', $exam->level_id)
            ->where('semester_id', $exam->semester_id)
            ->where('department_id', $exam->department_id)
            ->get(); // Get all matching exams

        // Return the found additional exams
        return $additionalExams;
    }
}
