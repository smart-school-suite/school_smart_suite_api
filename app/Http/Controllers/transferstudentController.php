<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Transferedstudents;
use App\Models\Transferrequest;
use Illuminate\Http\Request;

class transferstudentController extends Controller
{
    public function get_my_transfers(Request $request, $student_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $find_student = Student::find($student_id);
        if(!$find_student){
            return response()->json(['message' => 'student not found'], 400);
        }
        
        $my_transfers = Transferrequest::where('student_id', $find_student->id)
                          ->where('status', 'true')->get();

        return response()->json([
            'my_transfers' => $my_transfers,
            $currentSchool
        ], 200);
    }

    public function get_student_transfers(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $transfered_students = Transferedstudents::where('school_branch_id', $currentSchool->id)
                                ->get();
        return response()->json(['transfered_students' => $transfered_students], 200);
    }


}
