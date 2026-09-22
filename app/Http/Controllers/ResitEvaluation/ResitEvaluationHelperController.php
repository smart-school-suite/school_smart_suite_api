<?php

namespace App\Http\Controllers\ResitEvaluation;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Services\ResitExamEvaluation\ResitEvaluationHelperService;
use Illuminate\Http\Request;

class ResitEvaluationHelperController extends Controller
{
    protected ResitEvaluationHelperService $resitEvaluationHelperService;
    public function __construct(ResitEvaluationHelperService $resitEvaluationHelperService)
    {
        $this->resitEvaluationHelperService = $resitEvaluationHelperService;
    }

    public function resitEvaluationHelperData(Request $request, string $candidateId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $helperData = $this->resitEvaluationHelperService->getResitEvaluationHelper($candidateId, $currentSchool);
        return ApiResponseService::success("Resit Evaluation Helper Data Fetched Successfully", $helperData, null, 200);
    }

    public function updateResitEvaluationHelper(Request $request, string $candidateId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $helperData = $this->resitEvaluationHelperService->getResitUpdateEvaluationHelper($candidateId, $currentSchool);
        return ApiResponseService::success("Resit Update Evaluation Helper Data Fetched Successfully", $helperData, null, 200);
    }
}
