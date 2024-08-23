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
        $schoolId = $request->route('school');
        
        // Find the school based on the ID
        $school = Schoolbranches::findOrFail($schoolId);

        // Set the current school in the request so it can be used in controllers
        $request->attributes->set('currentSchool', $school);

        // Determine which guard is being accessed
        $user = Auth::guard('schooladmin')->user() 
                ?? Auth::guard('parent')->user() 
                ?? Auth::guard('teacher')->user()
                ?? Auth::guard('student')->user();

        if ($user) {
            // Ensure the user belongs to the identified school
            if ($user->school_id !== $school->id) {
                abort(403, 'Unauthorized access to this school data.');
            }
        }

        return $next($request);
    }
}
