<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subscription\RenewSubscriptionPlanRequest;
use App\Http\Requests\Subscription\SchoolSubscriptionRequest;
use App\Http\Requests\Subscription\UpgradeSubscriptionPlanRequest;
use App\Services\Subscription\SchoolSubscriptionService;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\SubscriptionPlan\PlanRecommendationService;

class SchoolSubscriptionController extends Controller
{
    protected SchoolSubscriptionService $schoolSubcriptionService;
    protected PlanRecommendationService $planRecommendationService;

    public function __construct(
        SchoolSubscriptionService $schoolSubcriptionService,
        PlanRecommendationService $planRecommendationService
    ) {
        $this->schoolSubcriptionService = $schoolSubcriptionService;
        $this->planRecommendationService = $planRecommendationService;
    }

    public function recommendPlan(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $recommendedPlan = $this->planRecommendationService->recommendPlan($currentSchool);
        return ApiResponseService::success("Recommended Plan Fetched Successfully", $recommendedPlan, null, 200);
    }

    /**
     * Current subscription, usage per feature, every plan compared with the
     * current one and the usage-based recommendation, in one payload.
     */
    public function getPlanInsights(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $insights = $this->planRecommendationService->getPlanInsights($currentSchool);
        return ApiResponseService::success("Plan Insights Fetched Successfully", $insights, null, 200);
    }
    public function subscribe(SchoolSubscriptionRequest $request)
    {
        $schoolSubcription = $this->schoolSubcriptionService->subscribe($request->validated());
        return ApiResponseService::success("School Subcription Was Succesfully", $schoolSubcription, null, 200);
    }
    public function upgradePlan(UpgradeSubscriptionPlanRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $upgradePlan = $this->schoolSubcriptionService->upgradePlan($request->validated(), $currentSchool);
        return ApiResponseService::success("Plan Upgraded Successfully", $upgradePlan, null, 200);
    }
    public function renewPlan(RenewSubscriptionPlanRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $renewPlan = $this->schoolSubcriptionService->renewSubscription($request->validated(), $currentSchool);
        return ApiResponseService::success("Subscription Plan Renewed Successfully", $renewPlan, null, 200);
    }
    public function getSchoolSubscriptions(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $subscriptions = $this->schoolSubcriptionService->getSchoolBranchSubscription($currentSchool);
        return ApiResponseService::success("School Subscription Fetched Successfully", $subscriptions, null, 200);
    }
}
