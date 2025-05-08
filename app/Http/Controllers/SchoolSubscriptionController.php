<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Subscription\SchoolSubscriptionRequest;
use App\Services\SchoolSubcriptionService;
use App\Services\ApiResponseService;

class SchoolSubscriptionController extends Controller
{
    //
    protected SchoolSubcriptionService $schoolSubcriptionService;

    public function __construct(SchoolSubcriptionService $schoolSubcriptionService)
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

    public function getSchoolSubscriptonDetails(Request $request, $subscription_id)
    {
        $subscriptionDetails = $this->schoolSubcriptionService->subcriptionPlanDetails($subscription_id);
        return ApiResponseService::success("Subcription Details Fetched Sucessfully", $subscriptionDetails, null, 200);
    }
}
