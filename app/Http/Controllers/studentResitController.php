<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Studentresit;

class studentResitController extends Controller
{
    //
    public function update_student_resit(Request $request, $resit_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $resit_id = $request->route('resit_id');
        $find_student_resit = Studentresit::where('school_branch_id', $currentSchool->id)
                                            ->find($resit_id);
       if(!$find_student_resit){
         return response()->json([
            'status' => 'error',
            'message' => 'student resit not found'
         ], 400);
       }

       $fillable_data = $request->all();
       $filtered_data = array_filter($fillable_data);
       $find_student_resit->fill($filtered_data);

       $find_student_resit->save();

       return response()->json([
           'status' => 'ok',
           'message' => 'Resit entry updated succefully',
           'updated_record' => $find_student_resit,
       ], 200);
    }

    public function pay_for_resit(Request $request, $resit_id){
        $request->validate([
            'paid_status' => 'required|string'
        ]);
        $currentSchool = $request->attributes->get('currentSchool');
        $resit_id = $request->route('resit_id');
        $find_student_resit = Studentresit::where('school_branch_id', $currentSchool->id)->find($resit_id);
        if(!$find_student_resit){
            return response()->json([
                'status' => 'error',
                'message' => 'student resit record not found'
            ], 400);
        }
         
        $find_student_resit->paid_status = $request->paid_status;

        $find_student_resit->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'student resit paid succefully',
            'paid_course' => $find_student_resit,
        ], 201);
    }

    public function update_exam_status(Request $request, $resit_id){
        $request->validate([
            'exam_status' => 'required|string'
        ]);
        $resit_id = $request->route('resit_id');
        $currentSchool = $request->attributes->get('currentSchool');
        $find_student_resit = Studentresit::where('school_branch_id', $currentSchool->id)->find($resit_id);
        if(!$find_student_resit){
            return response()->json([
                'status' => 'error',
                'message' => 'student resit record not found'
            ], 400);
        }

        $find_student_resit->exam_status = $request->exam_status;
        $find_student_resit->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'exam status update succefully',
            'updated_resit_record' => $find_student_resit,
        ], 200);
    }

    public function delete_student_resit_record(Request $request, $resit_id){
        $resit_id = $request->route('resit_id');
        $currentSchool = $request->attributes->get('currentSchool');
        $find_student_resit = Studentresit::where('school_branch_id', $currentSchool->id)->find($resit_id);
        if(!$find_student_resit){
            return response()->json([
                'status' => 'error',
                'message' => 'student resit record not found'
            ], 400);
        }
        $find_student_resit->delete();
        return response()->json([
            'status' => 'ok',
            'message' => 'Resit record deleted sucessfully',
            'deleted_resit_record' => $find_student_resit,
        ], 200);
    }

    public function get_my_resits(Request $request, $student_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $student_id = $request->route('student_id');
        $get_resit_data = Studentresit::where('school_branch_id', $currentSchool->id)
                                       ->where('student_id', $student_id)
                                        ->get();
        if($get_resit_data->isEmpty()){
            return response()->json([
                'status' => 'ok',
                'message' => 'Congratulations you have no resits'
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'student records fetched succefully',
            'resits' => $get_resit_data
        ], 200);
    }
}
