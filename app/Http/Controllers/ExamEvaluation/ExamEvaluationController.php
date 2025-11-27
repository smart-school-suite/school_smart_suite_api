<?php

namespace App\Http\Controllers\ExamEvaluation;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExamScore\CreateExamScoreRequest;
use App\Http\Requests\ExamScore\UpdateExamScoreRequest;
use App\Services\ApiResponseService;
use App\Services\ExamEvaluation\AddExamScoreService;
use App\Services\ExamEvaluation\UpdateExamScoreService;
use Exception;
class ExamEvaluationController extends Controller
{
    protected AddExamScoreService $addExamScoresService;
    protected UpdateExamScoreService $updateExamScoreService;
    public function __construct(
        AddExamScoreService $addExamScoresService,
        UpdateExamScoreService $updateExamScoreService
    ) {
        $this->addExamScoresService = $addExamScoresService;
        $this->updateExamScoreService = $updateExamScoreService;
    }

    public function createExamMark(CreateExamScoreRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $results = $this->addExamScoresService->addExamScores($request->scores_entries, $currentSchool);
        return ApiResponseService::success("MarkS Submitted Sucessfully", $results, null, 201);
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
}
