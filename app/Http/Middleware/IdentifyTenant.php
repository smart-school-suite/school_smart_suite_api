<?php

namespace App\Http\Middleware;

use App\Models\Schoolbranches;
use Closure;
use Illuminate\Http\Request;
use App\Services\ApiResponseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        $authUser = Auth::user();

        if (!$authUser) {
        }

        $schoolBranch = Schoolbranches::find($authUser->school_branch_id);
        if (!$schoolBranch) {
            return ApiResponseService::error("School Branch Not found", null, 404);
        }

        $request->attributes->set('currentSchool', $schoolBranch);

        return $next($request);
    }
}
