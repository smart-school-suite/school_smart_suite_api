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
        $authAdmin = $this->resolveUser();
        $results = $this->addCaScoresService->addCaScore($request->scores_entries, $currentSchool, $authAdmin);
        return ApiResponseService::success("Marks Submitted Sucessfully", $results, null, 201);
    }

        public function updateCaMark(UpdateExamScoreRequest $request)
    {
          $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
            $results = $this->updateCaScoresService->updateCaScore($request->scores_entries, $currentSchool, $authAdmin);
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
