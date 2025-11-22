<?php

namespace App\Http\Controllers;

use App\Services\ApiResponseService;
use App\Services\StudentResultService;
use App\Http\Resources\StudentResultResource;
use Illuminate\Http\Request;

class StudentResultController extends Controller
{
    //
    protected $studentResultService;
    public function __construct(StudentResultService $studentResultService)
    {
        $this->studentResultService = $studentResultService;
    }
    public function getAllStudentResults(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $studentResults = $this->studentResultService->getAllStudentResults($currentSchool);
        return ApiResponseService::success("Student Results Fetched Successfully", StudentResultResource::collection($studentResults), null, 200);
    }
    public function getMyResults(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $examId = $request->route('examId');
        $studentId = $request->route('studentId');
        $getMyResults = $this->studentResultService->getMyResults($currentSchool, $examId, $studentId);
        return ApiResponseService::success("Student Results Fetched Successfully", $getMyResults, null, 200);
    }
    public function generateStudentResultStandingPdfByExam(Request $request, $examId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $this->studentResultService->generateExamStandingsResultPdf($examId, $currentSchool);
    }
    public function getStandingsByExam(Request $request, $examId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $studentStandings = $this->studentResultService->getExamStandings($examId, $currentSchool);
        return ApiResponseService::success("Exam Standings Fetched Successfully", $studentStandings, null, 200);
    }
    public function generateStudentResultPdf(Request $request)
    {
        $studentId = $request->route('studentId');
        $currentSchool = $request->attributes->get("currentSchool");
        $examId = $request->route('examId');
        $this->studentResultService->generateStudentResultsPdf($examId, $studentId, $currentSchool);
    }

    public function getResultDetails(Request $request){
        $resultId = $request->route('resultId');
        $currentSchool = $request->attributes->get('currentSchool');
        $examResults = $this->studentResultService->getResultDetails($currentSchool, $resultId);
        return ApiResponseService::success("Exam Results Fetched Successfully", $examResults, null, 200);
    }
}
