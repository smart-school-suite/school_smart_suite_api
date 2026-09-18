<?php

namespace App\Http\Controllers\ExamEvaluation;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExamEvaluation\UpdateExamScoreRequest;
use App\Http\Requests\ExamEvaluation\CreateExamScoreRequest;
use App\Services\ApiResponseService;
use App\Services\ExamEvaluation\AddExamScoreService;
use App\Services\ExamEvaluation\UpdateExamScoreService;
use App\Services\ExamEvaluation\AddCaScoresService;
use App\Services\ExamEvaluation\UpdateCaScoreService;
class ExamEvaluationController extends Controller
{
    protected AddExamScoreService $addExamScoresService;
    protected UpdateExamScoreService $updateExamScoreService;
    protected AddCaScoresService $addCaScoresService;
    protected UpdateCaScoreService $updateCaScoresService;
    public function __construct(
        AddExamScoreService $addExamScoresService,
        UpdateExamScoreService $updateExamScoreService,
        AddCaScoresService $addCaScoresService,
        UpdateCaScoreService $updateCaScoreService
    ) {
        $this->addExamScoresService = $addExamScoresService;
        $this->updateExamScoreService = $updateExamScoreService;
        $this->updateCaScoresService = $updateCaScoreService;
        $this->addCaScoresService = $addCaScoresService;
    }

    public function createCaMark(CreateExamScoreRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $results = $this->addCaScoresService->addCaScore($request->validated(), $currentSchool, $authAdmin);
        return ApiResponseService::success("Marks Submitted Sucessfully", $results, null, 201);
    }

    public function updateCaMark(UpdateExamScoreRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $results = $this->updateCaScoresService->updateCaScore($request->validated(), $currentSchool);
        return ApiResponseService::success("MarkS Updated Sucessfully", $results, null, 201);
    }
    public function createExamMark(CreateExamScoreRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $results = $this->addExamScoresService->addExamScores($request->validated(), $currentSchool, $authAdmin);
        return ApiResponseService::success("MarkS Submitted Sucessfully", $results, null, 201);
    }

    public function updateExamMark(UpdateExamScoreRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $results = $this->updateExamScoreService->updateExamScore($request->validated(), $currentSchool, $authAdmin);
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
