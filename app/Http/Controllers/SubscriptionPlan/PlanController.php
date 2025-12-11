<?php

namespace App\Http\Controllers\SubscriptionPlan;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubscriptionPlan\CreatePlanRequest;
use App\Http\Requests\SubscriptionPlan\UpdatePlanRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\SubscriptionPlan\PlanService;
class PlanController extends Controller
{
    protected PlanService $planService;
    public function __construct(PlanService $planService)
    {
        $this->planService = $planService;
    }

    public function createPlan(CreatePlanRequest $request){
        $createPlan = $this->planService->createPlan($request->validated());
        return ApiResponseService::success("Plan Created Successfully", $createPlan, null, 201);
    }

    public function updatePlan(UpdatePlanRequest $request, $planId){
         $updatePlan = $this->planService->updatePlan($request->validated(), $planId);
         return ApiResponseService::success("Plan Updated Successfully", $updatePlan, null, 200);
    }

    public function deletePlan(Request $request, $planId){
         $deletePlan = $this->planService->deletePlan($planId);
         return ApiResponseService::success("Plan Deleted Successfully", $deletePlan, null, 200);
    }

    public function getAllPlans(Request $request){
         $deletePlan = $this->planService->getAllPlans();
         return ApiResponseService::success("Plans Fetched Successfully", $deletePlan, null, 200);
    }

    public function getPlanCountryId(Request $request, $countryId){
         $plans = $this->planService->getActivePlansCountryId($countryId);
         return ApiResponseService::success("Active Plans Fetched Successfully", $plans, null, 200);
    }

    public function activatePlan(Request $request, $planId){
         $plan = $this->planService->activatePlan($planId);
         return ApiResponseService::success("Plan Activated Successfully", $plan, null, 200);
    }

    public function deactivatePlan(Request $request, $planId){
         $plan = $this->planService->deactivatePlan($planId);
         return ApiResponseService::success("Plan Deactivated Successfully", $plan, null, 200);
    }
}
