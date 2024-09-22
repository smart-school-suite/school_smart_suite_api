<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\School;
use App\Models\Student;
use Symfony\Component\HttpFoundation\Response;

class Limitstudents
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $schoolId = $request->route('main_school');
        
        // Find the school based on the ID
        $school = School::with('subcription')->findOrFail($schoolId);

        if(!$school){
            return  response()->json([
                'status' => 'error',
                'message' => 'school branch not found'
            ], 400);
        }

        $studentsCount = Student::where('school_branch_id', $school->id)->count();
        if($studentsCount > $school->subcription->max_number_students){
            return response()->json([
                'status' => 'error',
                'message' => 'You have reached your student limit'
            ], 400);
        }
        return $next($request);
    }
}
