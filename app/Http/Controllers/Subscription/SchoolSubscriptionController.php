<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Subscription\SchoolSubscriptionRequest;
use App\Services\Subscription\SchoolSubscriptionService;
use App\Services\ApiResponseService;

class SchoolSubscriptionController extends Controller
{
    protected SchoolSubscriptionService $schoolSubcriptionService;

    public function __construct(SchoolSubscriptionService $schoolSubcriptionService)
    {
        $this->schoolSubcriptionService = $schoolSubcriptionService;
    }
    public function subscribe(SchoolSubscriptionRequest $request)
    {
        try {
            $schoolSubcription = $this->schoolSubcriptionService->subscribe($request->validated());
            return ApiResponseService::success("School Subcription Was Succesfully", $schoolSubcription, null, 200);
        } catch (\Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function getSubscribedSchools(Request $request)
    {
        $getAllSubcribedSchools = $this->schoolSubcriptionService->getAllSubcription();
        return ApiResponseService::success("Schoo Subcription Fetched Sucessfully", $getAllSubcribedSchools, null, 200);
    }

    public function getSchoolSubscriptonDetails($subscriptionId)
    {
        $subscriptionDetails = $this->schoolSubcriptionService->subcriptionPlanDetails($subscriptionId);
        return ApiResponseService::success("Subcription Details Fetched Sucessfully", $subscriptionDetails, null, 200);
    }
}
