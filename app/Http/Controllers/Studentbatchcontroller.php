<?php

namespace App\Http\Controllers;

use App\Models\Studentbatch;
use Illuminate\Http\Request;

class Studentbatchcontroller extends Controller
{
    //
    public function create_student_batch(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $request->validate([
             'name' => 'required|string'
        ]);

        $new_student_batch_instance = new Studentbatch();

        $new_student_batch_instance->name = $request->name;
        $new_student_batch_instance->school_branch_id = $currentSchool->id;

        $new_student_batch_instance->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'Student batch created succefully',
            'created_student_batch' => $new_student_batch_instance
        ], 200);
    }

    public function update_student_batch(Request $request){
        $batch_id = $request->route('batch_id');
        $currentSchool = $request->attributes->get('currentSchool');
        
        $find_student_batch = Studentbatch::where('school_branch_id',  $currentSchool->id)->find($batch_id);

        if(!$find_student_batch){
            return response()->json([
                'status' => 'error',
                'message' => 'student batch not found'
            ], 404);
        }

        $fillable_data = $request->all();
        $filtered_data = array_filter($fillable_data);
        $find_student_batch->fill($filtered_data);
        $find_student_batch->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'Student batch updated successfully',
            'update_student_batch' => $find_student_batch
        ], 200);
    }

    public function delete_student_batch(Request $request){
        $batch_id = $request->route('batch_id');
        $currentSchool = $request->attributes->get('currentSchool');
        
        $find_student_batch = Studentbatch::where('school_branch_id',  $currentSchool->id)->find($batch_id);

        if(!$find_student_batch){
            return response()->json([
                'status' => 'error',
                'message' => 'student batch not found'
            ], 404);
        }

        $find_student_batch->delete();

        return response()->json([
                'status' => 'ok',
                'message' => 'student deleted successfully',
                'deleted_student_batch' => $find_student_batch
        ], 200);
    }

    public function get_all_student_batches(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $student_batches_data = Studentbatch::where('school_branch_id', $currentSchool->id)->get();
        if($student_batches_data->isEmpty()){
            return response()->json([
                'status' => 'error',
                'message' => 'No student batch has been created'
            ], 400);
        }
        return response()->json([
            'status' => 'ok',
            'message' => 'student batches fetched succefully',
            'student_batches' => $student_batches_data
        ], 200);
    }
}
