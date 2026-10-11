<?php

namespace App\Http\Controllers\ResitEvaluation;

use App\Http\Controllers\Controller;
use App\Services\ResitExamEvaluation\AddResitScoreService;
use App\Services\ResitExamEvaluation\UpdateResitScoreService;
use App\Http\Requests\ResitExamScore\CreateResitExamScore;
use App\Http\Requests\ResitExamScore\UpdateResitExamScore;
use App\Services\ApiResponseService;

class ResitEvaluationController extends Controller
{
    protected AddResitScoreService $resitScoresService;

    protected UpdateResitScoreService $updateResitScoreService;

    public function __construct(
        AddResitScoreService $resitScoresService,
        UpdateResitScoreService $updateResitScoreService
    ) {
        $this->resitScoresService = $resitScoresService;
        $this->updateResitScoreService = $updateResitScoreService;
    }

    public function submitResitScores(CreateResitExamScore $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $resitScores = $this->resitScoresService->addStudentResitScores($request->validated(), $currentSchool);
        return ApiResponseService::success("Resit Scores Submitted Successfully", $resitScores, null, 200);
    }
    public function updateResitScores(UpdateResitExamScore $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateResitScores = $this->updateResitScoreService->updateStudentResitScores(
            $request->validated(),
            $currentSchool
        );
        return ApiResponseService::success("Student Resit Scores Updated Successfully", $updateResitScores, null, 200);
    }
}
