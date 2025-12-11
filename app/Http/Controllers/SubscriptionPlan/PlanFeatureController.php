<?php

namespace App\Http\Controllers\SubscriptionPlan;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubscriptionPlan\AssignFeatureToPlanRequest;
use App\Http\Requests\SubscriptionPlan\RemoveAssignedFeatureRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\SubscriptionPlan\PlanFeatureService;
class PlanFeatureController extends Controller
{
    protected PlanFeatureService $planFeatureService;
    public function __construct(PlanFeatureService $planFeatureService)
    {
        $this->planFeatureService = $planFeatureService;
    }
    public function assignFeatureToPlan(AssignFeatureToPlanRequest $request){
         $this->planFeatureService->assignFeatureToPlan($request->validated());
         return ApiResponseService::success("Feature Assigned Successfully", null, null, 201);
    }
    public function removeAssignedFeatures(RemoveAssignedFeatureRequest $request){
       $this->planFeatureService->removeAssignedFeature($request->validated());
       return ApiResponseService::success("Assigned Features Removed Successfully", null, null, 200);
    }

    public function getAssignableFeatures(Request $request, $planId){
       $features = $this->planFeatureService->getAssignableFeatures($planId);
       return ApiResponseService::success("Assignable Features Fetched Successfully", $features, null, 200);
    }

    public function getAssignedFeatures(Request $request, $planId){
        $features = $this->planFeatureService->getAssignedFeatures($planId);
        return ApiResponseService::success("Assigned Features Fetched Successfully", $features, null, 200);
    }
}
