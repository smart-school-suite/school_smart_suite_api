<?php

namespace App\Http\Controllers\ResitResult;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResitResult\PublishResitResultRequest;
use App\Http\Resources\ResitResult\ResitResultResource;
use App\Services\ApiResponseService;
use App\Services\ResitResult\ResitResultService;
use Illuminate\Http\Request;

class ResitResultController extends Controller
{
    public function __construct(protected ResitResultService $resitResultService) {}

    public function getResitExams(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $exams = $this->resitResultService->getResitExams($currentSchool);
        return ApiResponseService::success("Resit Exams Fetched Successfully", ResitResultResource::collection($exams), null, 200);
    }

    public function publishResitResult(PublishResitResultRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $details = $this->resitResultService->publishResitResult($currentSchool, $this->resolveUser(), $request->validated());
        $message = $details['publication_status'] === 'scheduled' ? "Resit Results Scheduled Successfully" : "Resit Results Published Successfully";
        return ApiResponseService::success($message, $details, null, 200);
    }

    public function getPublishDetails(Request $request, string $resitExamId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $details = $this->resitResultService->getPublishDetails($currentSchool, $resitExamId);
        return ApiResponseService::success("Resit Result Publication Details Fetched Successfully", $details, null, 200);
    }

    public function getEvaluationProgress(Request $request, string $resitExamId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $progress = $this->resitResultService->getEvaluationProgress($currentSchool, $resitExamId);
        return ApiResponseService::success("Resit Evaluation Progress Fetched Successfully", $progress, null, 200);
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
