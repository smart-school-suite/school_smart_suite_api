<?php

namespace App\Http\Controllers\CAEvaluation;

use App\Http\Controllers\Controller;
use App\Services\CAEvaluation\AddCaScoresService;
use App\Services\CAEvaluation\UpdateCaScoreService;
use App\Services\ApiResponseService;
use App\Http\Requests\ExamScore\CreateExamScoreRequest;
use App\Http\Requests\ExamScore\UpdateExamScoreRequest;
use Exception;
class CAEvaluationController extends Controller
{
    protected AddCaScoresService $addCaScoresService;
    protected UpdateCaScoreService $updateCaScoresService;
    public function __construct(
        AddCaScoresService $addCaScoresService,
        UpdateCaScoreService $updateCaScoreService,
    ) {
        $this->addCaScoresService = $addCaScoresService;
        $this->updateCaScoresService = $updateCaScoreService;
    }

    public function createCaMark(CreateExamScoreRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $results = $this->addCaScoresService->addCaScore($request->scores_entries, $currentSchool);
        return ApiResponseService::success("Marks Submitted Sucessfully", $results, null, 201);
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
}
