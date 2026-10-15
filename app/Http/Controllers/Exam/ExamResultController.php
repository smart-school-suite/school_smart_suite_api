<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Controller;
use App\Http\Requests\Exam\PublishExamResultRequest;
use App\Http\Resources\ExamResultResource;
use App\Services\ApiResponseService;
use App\Services\Exam\ExamResultService;
use Illuminate\Http\Request;

class ExamResultController extends Controller
{
    protected ExamResultService $examResultService;
    public function __construct(ExamResultService $examResultService)
    {
        $this->examResultService = $examResultService;
    }

    public function getExamResultPublishDetails(Request $request, string $examId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $details = $this->examResultService->getExamResultPublishDetails($currentSchool, $examId);
        return ApiResponseService::success("Exam Result Publishement Details Fetched Successfully", $details, null, 200);
    }
    public function publishExamResult(PublishExamResultRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authUser = $this->resolveUser();
        $publishExamResult = $this->examResultService->publishExamResult($currentSchool, $authUser, $request->validated());
        return ApiResponseService::success("Exam Results Published Successfully", $publishExamResult, null, 200);
    }
    public function getExams(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $exams = $this->examResultService->getExams($currentSchool);
        return ApiResponseService::success("Exams Fetched Successfully", ExamResultResource::collection($exams), null, 200);
    }
    public function getExamStandings(Request $request, string $examId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $standings = $this->examResultService->getExamStandings($currentSchool, $examId);
        return ApiResponseService::success("Exam Result Standings Fetched Successfully", $standings, null, 200);
    }

    public function getExamEvaluationProgress(Request $request, string $examId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $progress =  $this->examResultService->getExamEvaluationProgress($currentSchool, $examId);
        return ApiResponseService::success("Exam Evaluation Progress Fetched Successfully", $progress, null, 200);
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
