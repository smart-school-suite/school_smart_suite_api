<?php

namespace App\Http\Controllers\ResitExam;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\ResitExam\ResitScoreService;

class ResitScoreController extends Controller
{
    protected ResitScoreService $ResitScoreService;
    public function __construct(ResitScoreService $resitScoreService)
    {
        $this->ResitScoreService = $resitScoreService;
    }

    public function getResitScoresCandidateId(Request $request, string $candidateId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $resitScores = $this->ResitScoreService->getResitScoresCandidateId($candidateId, $currentSchool);
        return ApiResponseService::success("Resit Scores Fetched Successfully", $resitScores, null, 200);
    }

    public function deleteResitScoresCandidateId(Request $request, string $candidateId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $deleteScores = $this->ResitScoreService->deleteResitScoresCandidateId($candidateId, $currentSchool);
        return ApiResponseService::success("Resit Scores Deleted Successfully", $deleteScores, null, 200);
    }
}
