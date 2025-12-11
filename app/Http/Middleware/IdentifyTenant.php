<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SchoolBranchApiKey;
use App\Services\ApiResponseService;
use Illuminate\Support\Facades\Hash;
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
        $providedKey = $request->header('API-KEY');

        $apiKeyRecord = SchoolBranchApiKey::with('schoolBranch')
            ->get()
            ->first(fn($record) => Hash::check($providedKey, $record->api_key));

        if (!$apiKeyRecord?->schoolBranch) {
            return ApiResponseService::error("school branch not found or api key invalid", null, 404);
        }

        $request->attributes->set('currentSchool', $apiKeyRecord->schoolBranch);

        return $next($request);
    }
}
