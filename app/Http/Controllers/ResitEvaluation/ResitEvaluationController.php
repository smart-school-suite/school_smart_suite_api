<?php

namespace App\Http\Controllers\ResitEvaluation;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentResit\StudentResitIdRequest;
use App\Http\Requests\StudentResit\StudentResitTransactionIdRequest;
use App\Http\Resources\ResitResource;
use Exception;
use Illuminate\Http\Request;
use App\Services\Resit\AddResitScoreService;
use App\Services\Resit\ResitService;
use App\Services\Resit\UpdateResitScoreService;
use App\Http\Requests\StudentResit\BulkUpdateStudentResitRequest;
use App\Http\Requests\ResitExamScore\CreateResitExamScore;
use App\Http\Requests\ResitExamScore\UpdateResitExamScore;
use App\Http\Requests\StudentResit\BulkPayStudentResitRequest;
use App\Http\Requests\StudentResit\PayResitFeeRequest;
use App\Http\Resources\StudentResitTransResource;
use App\Services\ApiResponseService;

class ResitEvaluationController extends Controller
{
    protected AddResitScoreService $resitScoresService;

    protected UpdateResitScoreService $updateResitScoreService;

    public function __construct(
        ResitService $studentResitService,
        AddResitScoreService $resitScoresService,
        UpdateResitScoreService $updateResitScoreService
    ) {
        $this->resitScoresService = $resitScoresService;
        $this->updateResitScoreService = $updateResitScoreService;
    }

    public function submitResitScores(CreateResitExamScore $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $candidateId = $request->route('candidateId');
        $resitScores = $this->resitScoresService->submitStudentResitScores($request->entries, $currentSchool, $candidateId);
        return ApiResponseService::success("Resit Scores Submitted Successfully", $resitScores, null, 200);
    }
    public function updateResitScores(UpdateResitExamScore $request)
    {
        try {
            $candidateId = $request->route('candidateId');
            $currentSchool = $request->attributes->get('currentSchool');
            $updateResitScores = $this->updateResitScoreService->updateResitScores(
                $request->entries,
                $currentSchool,
                $candidateId
            );
            return ApiResponseService::success("Student Resit Scores Updated Successfully", $updateResitScores, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 404);
        }
    }
}
