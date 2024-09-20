<?php

namespace App\Http\Controllers;

use App\Models\Educationlevels;
use App\Models\Specialty;
use App\Models\Student;
use Illuminate\Http\Request;

class studentpromotionController extends Controller
{
    //
    public function promote_student_to_another_class(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');

        $request->validate([
            'level_id' => 'required|string',
            'specialty_id' => 'required|string',
            'student_id' => 'required|string'
        ]);

        $find_student = Student::where('school_branch_id', $currentSchool->id)
                                 ->with(['level'])
                                 ->find($request->student_id);
        
        $find_specialty = Specialty::where('school_branch_id', $currentSchool->id)
                                    ->where('level_id', $request->level_id)
                                    ->find($request->specialty_id);

        $find_level = Educationlevels::find($request->level_id);
        if(!$find_student){
            return response()->json([
                'status' => 'ok',
                'message' => 'student not found',
            ], 400);
        }

        if(!$find_level){
            return response()->json([
                'status' => 'ok',
                'message' => 'level not found'
            ]);
        }

        if(!$find_specialty){
            return response()->json([
                'status' => 'ok',
                'message' => 'Specialty not found'
            ], 400);
        }

        if($find_student->total_fee_debt > 0){
            $find_student->total_fee_debt = $find_specialty->registration_fee + $find_specialty->school_fee + $find_student->total_fee_debt;
        }

        if($find_student->total_fee_debt == 0){
            $find_student->total_fee_debt = $find_specialty->registration_fee + $find_specialty->school_fee;
            $find_student->fee_status = 'owing';
            $find_student->specialty_id = $request->specialty_id;
            $find_student->level_id = $request->level_id;
        }

        $find_student->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'student promoted succefully',
            'from' => $find_student->level->name,
            'to' => $find_level->name
        ], 200);

    }
}
