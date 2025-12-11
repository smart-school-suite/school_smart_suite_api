<?php

namespace App\Http\Controllers\SubscriptionPlan;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubscriptionPlan\CreateFeatureRequest;
use App\Http\Requests\SubscriptionPlan\UpdateFeatureRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\SubscriptionPlan\FeatureService;

class FeatureController extends Controller
{
    protected FeatureService $featureService;
    public function __construct(FeatureService $featureService)
    {
        $this->featureService = $featureService;
    }
    public function createFeature(CreateFeatureRequest $request)
    {
        $feature = $this->featureService->createFeature($request->validated());
        return ApiResponseService::success("Feature Created Successfully", $feature, null, 201);
    }

    public function updateFeature(UpdateFeatureRequest $request, $featureId)
    {
        $feature = $this->featureService->updateFeature($request->validated(), $featureId);
        return ApiResponseService::success("Feature Updated Successfully", $feature, null, 200);
    }

    public function deleteFeature(Request $request, $featureId)
    {
        $feature = $this->featureService->deleteFeature($featureId);
        return ApiResponseService::success("Feature Deleted Successfully", $feature, null, 200);
    }

    public function getFeatures(Request $request)
    {
        $features = $this->featureService->getFeatures();
        return ApiResponseService::success("Features Fetched Successfully", $features, null, 200);
    }

    public function activateFeature(Request $request, $featureId)
    {
        $activateFeature = $this->featureService->activateFeature($featureId);
        return ApiResponseService::success("Feature Activated Successfully", $activateFeature, null, 200);
    }

    public function deactivateFeature(Request $request, $featureId)
    {
        $deactivateFeature = $this->featureService->deactivateFeature($featureId);
        return ApiResponseService::success("Feature Deactivated Successfully", $deactivateFeature, null, 200);
    }
}
