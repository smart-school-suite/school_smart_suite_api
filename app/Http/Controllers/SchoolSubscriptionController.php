<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchoolSubscription;
use Illuminate\Support\Facades\DB;
use App\Models\SubscriptionPayment;
use App\Http\Requests\SchoolSubscriptionRequest;
use App\Services\SchoolSubcriptionService;
use App\Models\RatesCard;
use App\Services\ApiResponseService;
use Illuminate\Support\Str;
use Carbon\Carbon;

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
            $this->schoolSubcriptionService->subscribe($request->validated());
            return ApiResponseService::success("School Subcription Was Succesfully");

        } catch (\Exception $e) {
            return ApiResponseService::error("Failed To Create School Subcription");
        }
    }

    public function get_all_subscribed_schools(Request $request)
    {
        $getAllSubcribedSchools = $this->schoolSubcriptionService->getAllSubcription();
        return ApiResponseService::success("Schoo Subcription Fetched Sucessfully", $getAllSubcribedSchools, null, 200);
    }

    public function subcription_details(Request $request, $subscription_id)
    {
        $subscription = SchoolSubscription::find($subscription_id);
        $subscriptionDetails = $this->schoolSubcriptionService->subcriptionPlanDetails($subscription_id);
        return ApiResponseService::success("Subcription Details Fetched Sucessfully", $subscriptionDetails, null, 200);
    }
}
