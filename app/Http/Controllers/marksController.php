<?php

namespace App\Http\Controllers;


use App\Models\Grades;
use App\Models\Exams;
use App\Models\Examtimetable;
use App\Http\Requests\AddStudentScoreRequest;
use App\Http\Requests\UpdateScoreRequest;
use App\Services\AddExamScoresService;
use App\Services\AddCaScoresService;
use App\Services\UpdateExamScoreService;
use App\Services\UpdateCaScoreService;
use App\Services\MarkService;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class MarksController extends Controller
{
    protected MarkService $markService;
    protected AddExamScoresService $addExamScoresService;
    protected AddCaScoresService $addCaScoresService;
    protected UpdateCaScoreService $updateCaScoresService;
    protected UpdateExamScoreService $updateExamScoreService;
    public function __construct(
        MarkService $markService,
        AddExamScoresService $addExamScoresService,
        AddCaScoresService $addCaScoresService,
        UpdateCaScoreService $updateCaScoreService,
        UpdateExamScoreService $updateExamScoreService
    ) {
        $this->addExamScoresService = $addExamScoresService;
        $this->addCaScoresService = $addCaScoresService;
        $this->updateCaScoresService = $updateCaScoreService;
        $this->updateExamScoreService = $updateExamScoreService;
        $this->markService = $markService;
    }
    public function createCaMark(AddStudentScoreRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $results = $this->addCaScoresService->addCaScore($request->scores_entries, $currentSchool);
            return ApiResponseService::success("MarkS Submitted Sucessfully", $results, null, 201);
        } catch (\Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }
    public function createExamMark(AddStudentScoreRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $results = $this->addExamScoresService->addExamScores($request->scores_entries, $currentSchool);
            return ApiResponseService::success("MarkS Submitted Sucessfully", $results, null, 201);
        } catch (\Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }
    public function updateExamMark(UpdateScoreRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $results = $this->updateExamScoreService->updateExamScore($request->scores_entries, $currentSchool);
            return ApiResponseService::success("MarkS Updated Sucessfully", $results, null, 201);
        } catch (\Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }
    public function updateCaMark(UpdateScoreRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $results = $this->updateCaScoresService->updateCaScore($request->scores_entries, $currentSchool);
            return ApiResponseService::success("MarkS Updated Sucessfully", $results, null, 201);
        } catch (\Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    public function deleteMark(Request $request, $mark_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteScore = $this->markService->deleteMark($mark_id, $currentSchool);
        return ApiResponseService::success('Student Mark Deleted Sucessfully', $deleteScore, null, 200);
    }

    public function getMarksByExamStudent(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $exam_id = $request->route('exam_id');
        $student_id = $request->route('student_id');
        $allStudentScores = $this->markService->getStudentScores($student_id, $currentSchool, $exam_id);
        return ApiResponseService::success('Scores Fetched Sucessfully', $allStudentScores, null, 200);
    }

    public function getAllMarks(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $studentDetails = $this->markService->getAllStudentsScores($currentSchool);
        return ApiResponseService::success("Student Scores Fetched Succesfully", $studentDetails, null, 200);
    }

    public function getMarkDetails(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $mark_id = $request->route("mark_id");
        $markDetails = $this->markService->getScoreDetails($currentSchool, $mark_id);
        return ApiResponseService::success("Scores Detailed Fetched Successfully", $markDetails, null, 200);
    }

    //revisit this code and update the resources file
    public function getAccessedCoursesWithLettergrades(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $examId = $request->route("examId");
        $exam = Exams::findOrFail($examId);
        $accessedCourses = Examtimetable::where("school_branch_id", $currentSchool->id)
            ->where("exam_id", $examId)
            ->with(["course"])
            ->get();

        $resultsOne = [];
        foreach ($accessedCourses as $course) {

            $resultsOne[] = [
                "course_id" => $course->course->id,
                "course_name" => $course->course->course_title,
                "course_credit" => $course->course->credit,
                "exam_id" => $examId,
                "weighted_mark" => $exam->weighted_mark,
            ];
        }

        $resultsTwo = [];
        $examGrades = Grades::where("school_branch_id", $currentSchool->id)
            ->where("grades_category_id", $exam->grades_category_id)
            ->with(["lettergrade"])
            ->get();

        foreach ($examGrades as $grade) {

            $resultsTwo[] = [
                "id" => $grade->id,
                "letter_grade" => $grade->lettergrade->letter_grade,
                "grade_points" => $grade->grade_points,
                "minimum_score" => $grade->minimum_score,
                "maximum_score" => $grade->maximum_score,
                "grade_status" => $grade->grade_status,
                "determinant" => $grade->determinant,

            ];
        }

        return response()->json([
            "status" => "ok",
            "message" => "Data fetched successfully",
            "accessed_courses" => $resultsOne,
            "grades_determinant" => $resultsTwo
        ], 200);
    }
}
