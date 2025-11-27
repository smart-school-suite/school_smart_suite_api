<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Services\Exam\ExamScoreService;
use Throwable;
use Illuminate\Http\Request;

class ExamScoreController extends Controller
{
    protected ExamScoreService $examScoreService;
    public function __construct(ExamScoreService $examScoreService)
    {
        $this->examScoreService = $examScoreService;
    }

    public function getExamMarksByCandidate(Request $request, $candidateId)
    {
        try {
            $currentSchool = $request->attributes->get('currentSchool');
            $examResults = $this->examScoreService->getExamMarksByExamCandidate($candidateId, $currentSchool);
            return ApiResponseService::success("Exam Marks Fetched Successfully", $examResults, null, 200);
        } catch (Throwable $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function getCaMarksByExamCandidate(Request $request, $candidateId)
    {
        try {
            $currentSchool = $request->attributes->get('currentSchool');
            $examResults = $this->examScoreService->getCaMarksByExamCandidate($candidateId, $currentSchool);
            return ApiResponseService::success("CA Marks Fetched Successfully", $examResults, null, 200);
        } catch (Throwable $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function deleteMark(Request $request, $markId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteScore = $this->examScoreService->deleteMark($markId, $currentSchool);
        return ApiResponseService::success('Student Mark Deleted Sucessfully', $deleteScore, null, 200);
    }
    public function getMarksByExamStudent(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $examId = $request->route('examId');
        $studentId = $request->route('studentId');
        $allStudentScores = $this->examScoreService->getStudentScores($studentId, $currentSchool, $examId);
        return ApiResponseService::success('Scores Fetched Sucessfully', $allStudentScores, null, 200);
    }
    public function getAllMarks(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $studentDetails = $this->examScoreService->getAllStudentsScores($currentSchool);
        return ApiResponseService::success("Student Scores Fetched Succesfully", $studentDetails, null, 200);
    }
    public function getMarkDetails(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $markId = $request->route("markId");
        $markDetails = $this->examScoreService->getScoreDetails($currentSchool, $markId);
        return ApiResponseService::success("Scores Detailed Fetched Successfully", $markDetails, null, 200);
    }

    public function prepareCaResultsByExam(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $examId = $request->route("examId");
        $studentId = $request->route("studentId");
        $prepareCaResults = $this->examScoreService->prepareCaDataByExam($currentSchool, $studentId, $examId);
        return ApiResponseService::success("Scores Detailed Fetched Successfully", $prepareCaResults, null, 200);
    }
    public function prepareCaData(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $examId = $request->route("examId");
        $studentId = $request->route("studentId");
        $prepareCaResults = $this->examScoreService->prepareCaData($currentSchool, $studentId, $examId);
        return ApiResponseService::success("Scores Detailed Fetched Successfully", $prepareCaResults, null, 200);
    }
    public function prepareExamData(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $examId = $request->route("examId");
        $studentId = $request->route("studentId");
        $prepareExamResults = $this->examScoreService->prepareExamData($currentSchool, $studentId, $examId);
        return ApiResponseService::success("Scores Detailed Fetched Successfully", $prepareExamResults, null, 200);
    }

    public function getCaEvaluationHelperData(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $examId = $request->route("examId");
        $evaluationData = $this->examScoreService->getCaExamEvaluationHelperData($currentSchool, $examId);
        return ApiResponseService::success("CA Evaluation Helper Data Fetched Successfully", $evaluationData, null, 200);
    }

    public function getExamEvaluationHelperData(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $examId = $request->route('examId');
        $studentId = $request->route("studentId");
        $evaluationData = $this->examScoreService->getExamEvaluationHelperData($currentSchool, $examId, $studentId);
        return ApiResponseService::success("Exam Evaluation Helper Data Fetched Successfully", $evaluationData, null, 200);
    }
}
