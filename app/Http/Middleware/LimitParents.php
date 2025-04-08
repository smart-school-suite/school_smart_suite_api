<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SchoolBranchApiKey;
use App\Models\SchoolSubscription;
use App\Services\ApiResponseService;
use Symfony\Component\HttpFoundation\Response;

class LimitParents
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $schoolBranchApiKey = $request->header('API-KEY');
        $schoolBranch = SchoolBranchApiKey::where("api_key", $schoolBranchApiKey)->with(['schoolBranch'])->first();
        if (!$schoolBranch) {
            return ApiResponseService::error("school branch not found or api key invalid", null, 404);
        }
        $subcriptionDetails = SchoolSubscription::where('school_branch_id', $schoolBranch->school_branch_id)->first();
        if ($schoolBranch->max_number_parents > $subcriptionDetails->max_number_parents) {
            return ApiResponseService::error("You have reached your max mimum parent creation limit : {$subcriptionDetails->max_number_parents}", null, 400);
        }
        return $next($request);
    }
}
