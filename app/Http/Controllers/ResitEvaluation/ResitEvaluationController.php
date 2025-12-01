<?php

namespace App\Http\Controllers\ResitEvaluation;

use App\Http\Controllers\Controller;
use Exception;
use App\Services\Resit\AddResitScoreService;
use App\Services\Resit\UpdateResitScoreService;
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
        $authAdmin = $this->resolveUser();
        $candidateId = $request->route('candidateId');
        $resitScores = $this->resitScoresService->submitStudentResitScores($request->entries, $currentSchool, $candidateId, $authAdmin);
        return ApiResponseService::success("Resit Scores Submitted Successfully", $resitScores, null, 200);
    }
    public function updateResitScores(UpdateResitExamScore $request)
    {
        $authAdmin = $this->resolveUser();
        $candidateId = $request->route('candidateId');
        $currentSchool = $request->attributes->get('currentSchool');
        $updateResitScores = $this->updateResitScoreService->updateResitScores(
            $request->entries,
            $currentSchool,
            $candidateId,
            $authAdmin
        );
        return ApiResponseService::success("Student Resit Scores Updated Successfully", $updateResitScores, null, 200);
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
