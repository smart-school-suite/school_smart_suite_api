<?php

namespace App\Http\Controllers;


use App\Models\Grades;
use App\Models\Exams;
use App\Models\Examtimetable;
use App\Services\AddExamScoresService;
use App\Http\Requests\ExamScore\CreateExamScoreRequest;
use App\Http\Requests\ExamScore\UpdateExamScoreRequest;
use App\Services\AddCaScoresService;
use App\Services\UpdateExamScoreService;
use App\Services\UpdateCaScoreService;
use App\Services\MarkService;
use App\Services\ApiResponseService;
use Exception;
use Throwable;
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
    public function getExamMarksByCandidate(Request $request, $candidateId){
        try{
            $currentSchool = $request->attributes->get('currentSchool');
            $examResults = $this->markService->getExamMarksByExamCandidate($candidateId, $currentSchool);
            return ApiResponseService::success("Exam Marks Fetched Successfully", $examResults, null, 200);
        }
        catch(Throwable $e){
             return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function getCaMarksByExamCandidate(Request $request, $candidateId){
        try{
            $currentSchool = $request->attributes->get('currentSchool');
            $examResults = $this->markService->getCaMarksByExamCandidate($candidateId, $currentSchool);
            return ApiResponseService::success("CA Marks Fetched Successfully", $examResults, null, 200);
        }
        catch(Throwable $e){
             return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function createCaMark(CreateExamScoreRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $results = $this->addCaScoresService->addCaScore($request->scores_entries, $currentSchool);
            return ApiResponseService::success("MarkS Submitted Sucessfully", $results, null, 201);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }
    public function createExamMark(CreateExamScoreRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $results = $this->addExamScoresService->addExamScores($request->scores_entries, $currentSchool);
            return ApiResponseService::success("MarkS Submitted Sucessfully", $results, null, 201);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }
    public function updateExamMark(UpdateExamScoreRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $results = $this->updateExamScoreService->updateExamScore($request->scores_entries, $currentSchool);
            return ApiResponseService::success("MarkS Updated Sucessfully", $results, null, 201);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }
    public function updateCaMark(UpdateExamScoreRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $results = $this->updateCaScoresService->updateCaScore($request->scores_entries, $currentSchool);
            return ApiResponseService::success("MarkS Updated Sucessfully", $results, null, 201);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }
    public function deleteMark(Request $request, $markId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteScore = $this->markService->deleteMark($markId, $currentSchool);
        return ApiResponseService::success('Student Mark Deleted Sucessfully', $deleteScore, null, 200);
    }
    public function getMarksByExamStudent(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $examId = $request->route('examId');
        $studentId = $request->route('studentId');
        $allStudentScores = $this->markService->getStudentScores($studentId, $currentSchool, $examId);
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
        $markId = $request->route("markId");
        $markDetails = $this->markService->getScoreDetails($currentSchool, $markId);
        return ApiResponseService::success("Scores Detailed Fetched Successfully", $markDetails, null, 200);
    }
    public function prepareCaResultsByExam(Request $request){
        $currentSchool = $request->attributes->get("currentSchool");
        $examId = $request->route("examId");
        $studentId = $request->route("studentId");
        $prepareCaResults = $this->markService->prepareCaDataByExam($currentSchool, $studentId, $examId);
        return ApiResponseService::success("Scores Detailed Fetched Successfully", $prepareCaResults, null, 200);
    }
    public function prepareCaData(Request $request){
        $currentSchool = $request->attributes->get("currentSchool");
        $examId = $request->route("examId");
        $studentId = $request->route("studentId");
        $prepareCaResults = $this->markService->prepareCaData($currentSchool, $studentId, $examId);
        return ApiResponseService::success("Scores Detailed Fetched Successfully", $prepareCaResults, null, 200);
    }
    public function prepareExamData(Request $request){
        $currentSchool = $request->attributes->get("currentSchool");
        $examId = $request->route("examId");
        $studentId = $request->route("studentId");
        $prepareExamResults = $this->markService->prepareExamData($currentSchool, $studentId, $examId);
        return ApiResponseService::success("Scores Detailed Fetched Successfully", $prepareExamResults, null, 200);
    }

    //revisit this code and update the resources file
    public function getAccessedCoursesWithLettergrades(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $examId = $request->route("examId");
        $exam = Exams::findOrFail($examId);
        $accessedCourses = Examtimetable::where("school_branch_id", $currentSchool->id)
            ->where("examId", $examId)
            ->with(["course"])
            ->get();

        $resultsOne = [];
        foreach ($accessedCourses as $course) {

            $resultsOne[] = [
                "course_id" => $course->course->id,
                "course_name" => $course->course->course_title,
                "course_credit" => $course->course->credit,
                "examId" => $examId,
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

    public function getCaEvaluationHelperData(Request $request){
        $currentSchool = $request->attributes->get("currentSchool");
        $examId = $request->route("examId");
        try{
             $evaluationData = $this->markService->getCaExamEvaluationHelperData($currentSchool, $examId);
             return ApiResponseService::success("CA Evaluation Helper Data Fetched Successfully", $evaluationData, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function getExamEvaluationHelperData(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $examId = $request->route('examId');
        $studentId = $request->route("studentId");
        try{
            $evaluationData = $this->markService->getExamEvaluationHelperData($currentSchool, $examId, $studentId);
            return ApiResponseService::success("Exam Evaluation Helper Data Fetched Successfully", $evaluationData, null, 200);
        }
        catch(Exception $e){
           return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
}
