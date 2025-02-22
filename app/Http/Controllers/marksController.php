<?php

namespace App\Http\Controllers;

use App\Models\Marks;
use App\Models\Grades;
use App\Models\Exams;
use App\Models\Examtimetable;
use App\Models\Student;
use App\Services\AddScoreService;
use App\Http\Requests\AddStudentScoreRequest;
use App\Services\MarkService;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class MarksController extends Controller
{
    protected AddScoreService $addScoreService;
    protected MarkService $markService;
    public function __construct(AddScoreService $addScoreService, MarkService $markService)
    {
        $this->addScoreService = $addScoreService;
        $this->markService = $markService;
    }
    public function add_student_mark(AddStudentScoreRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $results = $this->addScoreService->addStudentScores($request->student_scores, $currentSchool);
            return ApiResponseService::success("MarkS Submitted Sucessfully", $results, null, 201);
        } catch (\Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    public function delete_mark_of_student_scoped(Request $request, $mark_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteScore = $this->markService->deleteMark($mark_id, $currentSchool);
        return ApiResponseService::success('Student Mark Deleted Sucessfully', $deleteScore, null, 200);
    }

    //update student marks review this code
    public function update_student_mark_scoped(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $mark_id = $request->route('mark_id');
        $updateScore = $this->markService->updateMark($request->validated(), $mark_id, $currentSchool);
        return ApiResponseService::success('Student Score Updated Successfully', $updateScore, null, 200);
    }

    public function get_all_student_marks(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $exam_id = $request->route('exam_id');
        $student_id = $request->route('student_id');
        $allStudentScores = $this->markService->getStudentScores($student_id, $currentSchool, $exam_id);
        return ApiResponseService::success('Scores Fetched Sucessfully', $allStudentScores, null, 200);
    }

    public function get_all_student_scores(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $studentDetails = $this->markService->getAllStudentsScores($currentSchool);
        return ApiResponseService::success("Student Scores Fetched Succesfully", $studentDetails, null, 200);
    }

    public function get_exam_score_details(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $mark_id = $request->route("mark_id");
        $markDetails = $this->markService->getScoreDetails($currentSchool, $mark_id);
        return ApiResponseService::success("Scores Detailed Fetched Successfully", $markDetails, null, 200);
    }


    //revisit this code and update the resources file
    public function get_exam_score_associated_data(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $exam_id = $request->route("exam_id");
        $student_id = $request->route("student_id");

        $find_student = Student::find($student_id);
        $find_exam = Exams::find($exam_id);
        if (!$find_student) {
            return response()->json([
                'status' => 'error',
                'message' => 'Student not found'
            ], 400);
        }

        if (!$find_exam) {
            return response()->json([
                "status" => "error",
                "message" => "exam not found"
            ], 400);
        }

        $specailty_id = $find_student->specialty_id;
        $level_id = $find_student->level_id;
        $get_exam_courses = Examtimetable::where("school_branch_id", $currentSchool->id)
            ->where("exam_id", $exam_id)
            ->where("specialty_id", $specailty_id)
            ->with(["course"])
            ->get();

        $results = [];
        foreach ($get_exam_courses as $course) {

            $results[] = [
                "level_id" => $level_id,
                "course_id" => $course->course->id,
                "course_name" => $course->course->course_title,
                "exam_id" => $exam_id,
                "specailty_id" => $course->specialty_id,
                "weighted_mark" => $find_exam->weighted_mark,
                "student_id" => $student_id
            ];
        }

        $results_two = [];
        $grades_calculator_data = Grades::where("school_branch_id", $currentSchool->id)
            ->where("exam_id", $exam_id)
            ->with(["lettergrade"])
            ->get();

        foreach ($grades_calculator_data as $grade) {

            $results_two[] = [
                "id" => $grade->id,
                "letter_grade" => $grade->lettergrade->letter_grade,
                "grade_points" => $grade->grade_points,
                "minimum_score" => $grade->minimum_score
            ];
        }

        return response()->json([
            "status" => "ok",
            "message" => "Data fetched successfully",
            "accessed_courses" => $results,
            "grades_determinant" => $results_two
        ], 200);
    }
}
