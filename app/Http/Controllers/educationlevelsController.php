<?php

namespace App\Http\Controllers;

use App\Models\Educationlevels;
use Illuminate\Http\Request;

class educationlevelsController extends Controller
{
    public function create_education_levels(Request $request){
        $request->validate([
            'name' => 'required|string',
            'level' => 'required|string',
            'program_name' => 'required|string' 
        ]);

        $education_level_instance = new Educationlevels();

        $education_level_instance->name = $request->name;
        $education_level_instance->level = $request->level;
        $education_level_instance->program_name = $request->program_name;

        $education_level_instance->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'Education level created sucessfully',
            'created_level' => $education_level_instance
        ], 200);
    }

    public function update_education_levels(Request $request, $education_level_id){
         $education_level = Educationlevels::find($education_level_id);
         if(!$education_level){
            return response()->json([
                'status' => 'ok',
                'message' => 'Education level not found'
            ], 404);
         }

         $education_level_data = $request->all();
         $education_level_data = array_filter($education_level_data);
         $education_level->fill();

         return response()->json([
            'status' => 'ok',
            'message' => 'Education level updated sucessfully'
         ], 200);
    }

    public function delete_education_levels(Request $request, $education_level_id){
        $education_level = Educationlevels::find($education_level_id);
         if(!$education_level){
            return response()->json([
                'status' => 'ok',
                'message' => 'Education level not found'
            ], 404);
         }

         $education_level->delete();

         return response()->json([
            'status' => 'ok',
            'message' => 'Education level deleted succesfully'
         ], 200);
    }

    public function get_all_education_leves(Request $request){
        $education_levels = Educationlevels::all();
        if($education_levels->isEmpty()){
            return response()->json([
                'status' => 'ok',
                'message' => 'No records found'
            ], 400);
        }
        return response()->json([
            'status' => 'ok',
            'message' => 'records fetched succefully',
            'education_level' => $education_levels
        ], 200);
    }

    
}
