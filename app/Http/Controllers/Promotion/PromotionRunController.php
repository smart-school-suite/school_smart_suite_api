<?php

namespace App\Http\Controllers\Promotion;

use App\Http\Controllers\Controller;
use App\Http\Requests\PromotionRun\TriggerPromotionRunRequest;
use App\Services\ApiResponseService;
use App\Services\Promotion\PromotionRunService;
use Illuminate\Http\Request;

class PromotionRunController extends Controller
{
    protected PromotionRunService $promotionRunService;
    public function __construct(PromotionRunService $promotionRunService)
    {
        $this->promotionRunService = $promotionRunService;
    }
    public function getPromotionRuns(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $promotionRuns = $this->promotionRunService->getPromotionRuns($currentSchool);
        return ApiResponseService::success("Promotion Runs Fetched Successfully", $promotionRuns, null, 200);
    }
    public function triggerPromotionRun(TriggerPromotionRunRequest $request, string $runId)
    {
        $authUser = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $triggerPromotionRun = $this->promotionRunService->triggerPromotion($currentSchool, $authUser, $request->validated());
        return ApiResponseService::success("Promotion Run Triggered Successfully", $triggerPromotionRun, null, 200);
    }
    public function getPromotionRunLog(Request $request, string $runId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $promotionLogs = $this->promotionRunService->getPromotionRunLogs($currentSchool, $runId);
        return ApiResponseService::success("Promotion Run Log Fetched Successfully", $promotionLogs, null, 200);
    }

    public function retryPromotionRun(Request $request, string $runId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $retryRun = $this->promotionRunService->retryPromotionRun($currentSchool, $runId);
        return ApiResponseService::success("Promotion Run Initiated Successfully", $retryRun, null, 200);
    }
    public function getPromotionRunById(Request $request, string $runId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $run = $this->promotionRunService->getPromotionRunById($currentSchool, $runId);
        return ApiResponseService::success("Promotion Run Details Fetched Successfully", $run, null, 200);
    }

    public function cancelPromotionRun(Request $request, string $runId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $cancelRun = $this->promotionRunService->cancelPromotionRun($currentSchool, $runId);
        return ApiResponseService::success("Promotion Run Cancelled Successfully", $cancelRun, null, 200);
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
