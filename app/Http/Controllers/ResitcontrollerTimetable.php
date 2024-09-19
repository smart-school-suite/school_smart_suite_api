<?php

namespace App\Http\Controllers;

use App\Models\Resitablecourses;
use App\Models\Specialty;
use Illuminate\Http\Request;

class ResitcontrollerTimetable extends Controller
{
    //
    public function get_resits_for_specialty(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $specialty_id = $request->route('specialty_id');
        $exam_id = $request->route('exam_id');

        $find_specialty = Specialty::where('school_branch_id', $currentSchool->id)
                                    ->find($specialty_id);
                                    
        if (!$find_specialty) {
            return response()->json([
                'status' => 'error',
                'message' => 'Specialty not found'
            ], 404); // Use 404 for "not found"
        }
    
        // Retrieve resitable courses for the specified specialty and exam
        $resitable_courses = Resitablecourses::where('school_branch_id', $currentSchool->id)
                                             ->where('exam_id', $exam_id)
                                             ->where('specialty_id', $specialty_id)
                                             ->with(['courses'])
                                             ->get();
                                             
        if ($resitable_courses->isEmpty()) {
            return response()->json([
                'status' => 'ok',
                'message' => 'It appears there are no resits.'
            ], 404); // Use 404 for no resits found
        }
    
        return response()->json([
            'status' => 'ok',
            'message' => 'Resitable courses fetched successfully.',
            'courses' => $resitable_courses
        ], 200); // Use 200 for successful requests
    }
}
