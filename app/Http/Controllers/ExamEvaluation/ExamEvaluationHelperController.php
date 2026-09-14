<?php

namespace App\Http\Controllers\ExamEvaluation;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Services\ExamEvaluation\ExamEvalutionHelperService;
use Illuminate\Http\Request;

class ExamEvaluationHelperController extends Controller
{
    protected ExamEvalutionHelperService $examEvalutionHelperService;
    public function __construct(ExamEvalutionHelperService $examEvalutionHelperService)
    {
        $this->examEvalutionHelperService = $examEvalutionHelperService;
    }

    public function getCaExamEvaluationHelperData(Request $request, string $candidateId) {
         $currentSchool = $request->attributes->get('currentSchool');
         $helperData = $this->examEvalutionHelperService->getCaExamEvaluationHelperData($currentSchool, $candidateId);
         return ApiResponseService::success("Ca Exam Evaluation Helper Data Fetched Successfully", $helperData, null, 200);
    }

    public function getExamEvaluationHelperData(Request $request, string $candidateId){
         $currentSchool = $request->attributes->get('currentSchool');
         $helperData = $this->examEvalutionHelperService->getExamEvaluationHelperData($currentSchool, $candidateId);
         return ApiResponseService::success("Exam Evaluation Helper Data Fetched Successfully", $helperData, null, 200);
    }
}
