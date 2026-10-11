<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Services\Exam\ExamScoreService;
use Illuminate\Http\Request;

class ExamScoreController extends Controller
{
    protected ExamScoreService $examScoreService;
    public function __construct(ExamScoreService $examScoreService)
    {
        $this->examScoreService = $examScoreService;
    }

    public function getExamScoreCandidateId(Request $request, string $candidateId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $scores = $this->examScoreService->getExamScoresCandidateId($candidateId, $currentSchool);
        return ApiResponseService::success("Exam Scores Fetched Successfully", $scores, null, 200);
    }

    public function getCaExamScoreCandidateId(Request $request, string $candidateId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $scores = $this->examScoreService->getCaExamScoresCandidateId($candidateId, $currentSchool);
        return ApiResponseService::success("Ca Exam Scores Fetched Successfully", $scores, null, 200);
    }

    public function deleteExamScores(Request $request, string $candidateId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteScore = $this->examScoreService->deleteExamScoresCandidateId($candidateId, $currentSchool);
        return ApiResponseService::success("Exam Scores Deleted Successfully", $deleteScore, null, 200);
    }

    public function deleteCaScores(Request $request, string $candidateId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteScore = $this->examScoreService->deleteCaExamScoresCandidateId($candidateId, $currentSchool);
        return ApiResponseService::success("Ca Exam Scores Deleted Successfully", $deleteScore, null, 200);
    }
}
