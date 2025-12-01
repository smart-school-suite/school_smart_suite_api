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
        $authAdmin = $this->resolveUser();
        $results = $this->addExamScoresService->addExamScores($request->scores_entries, $currentSchool, $authAdmin);
        return ApiResponseService::success("MarkS Submitted Sucessfully", $results, null, 201);
    }

    public function updateExamMark(UpdateExamScoreRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $results = $this->updateExamScoreService->updateExamScore($request->scores_entries, $currentSchool, $authAdmin);
        return ApiResponseService::success("MarkS Updated Sucessfully", $results, null, 201);
    }
    protected function resolveUser()
    {
        foreach (['student', 'teacher', 'schooladmin'] as $guard) {
            $user = request()->user($guard);
            if ($user !== null) {
                return $user;
            }
        }
        return null;
    }
}
