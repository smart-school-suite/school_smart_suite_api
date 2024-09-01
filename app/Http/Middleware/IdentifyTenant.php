<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Schoolbranches;
use Illuminate\Support\Facades\Auth;
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
        $schoolId = $request->route('school_id');
        
        // Find the school based on the ID
        $school = Schoolbranches::findOrFail($schoolId);

        if(!$school){
            return redirect('/') && response()->json(['message' => 'school brnach not found']);
        }

        // Set the current school in the request so it can be used in controllers
        $request->attributes->set('currentSchool', $school);


        return $next($request);
    }
}
