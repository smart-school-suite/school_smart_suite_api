<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SchoolBranchApiKey;
use App\Services\ApiResponseService;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
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
        if(!$schoolBranch){
            return ApiResponseService::error("school branch not found or api key invalid", null, 404);
        }
        $request->attributes->set('currentSchool', $schoolBranch->schoolBranch);
        return $next($request);
    }
}
