<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SchoolBranchApiKey;
use App\Models\SchoolSubscription;
use App\Models\SubscriptionUsage;
use App\Services\ApiResponseService;
use Symfony\Component\HttpFoundation\Response;

class LimitTeachers
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    // public function handle(Request $request, Closure $next): Response
    // {
    //     $providedKey = $request->header('API-KEY');
    //     $apiKeyRecord = SchoolBranchApiKey::with('schoolBranch.school.country')
    //         ->where('api_key', $providedKey)
    //         ->first();
    //     if (!$providedKey) {
    //         return response()->json(
    //             [
    //                 'status' => 'error',
    //                 'message' => $message,
    //                 'data' => null,
    //                 'errors' => $errors,
    //                 'meta' => null
    //             ]
    //         );
    //     }
    //     if (!$apiKeyRecord) {
    //         return response()->json(
    //             [
    //                 'status' => 'error',
    //                 'message' => $message,
    //                 'data' => null,
    //                 'errors' => $errors,
    //                 'meta' => null
    //             ]
    //         );
    //     }

    //     $subscription = SchoolSubscription::where("school_branch_id", $apiKeyRecord->schoolBranch->id)
    //                                                   ->where("status", "active")
    //                                                   ->first();
    //     $planUsage = SubscriptionUsage::where("subscription_id", $subscription->id)
    //                                     ->with(['featurePlan.feature' => function ($query) {
    //                                          $query->featurePlan->feature->key = ""
    //                                     }])
    //                                     ->get();
    //     $planUsage

    //     return $next($request);
    // }
}
