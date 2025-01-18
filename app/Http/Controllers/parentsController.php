<?php

namespace App\Http\Controllers;

use App\Models\Parents;
use Illuminate\Http\Request;

class parentsController extends Controller
{
    public function get_all_parents_within_a_School_without_relations(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $parents = Parents::Where('school_branch_id', $currentSchool->id)->get();
        return response()->json(['parents' => $parents], 200);
    }

    public function get_all_parents_with_relations_without_scope(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $parents = Parents::Where('school_branch_id', $currentSchool->id)->with('student')->get();
        if($parents->isEmpty()){
            return response()->json([
                 'status' => 'ok',
                 'message' => 'parent data appears to be empty'
            ], 409);
        }
        return response()->json(['parent' => $parents], 200);
    }
    
    public function delete_parent_with_scope(Request $request, $parent_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $parent_data = Parents::Where('school_branch_id', $currentSchool->id)->find($parent_id);
        if(!$parent_data){
            return response()->json([
                'status' => 'error',
                'message' => 'Parent not found'
            ], 409);
        }

        $parent_data->delete();

        return response()->json([
            'status' => 'ok',
            'message' => 'Parent deleted sucessfully',
            'deleted_parent' => $parent_data
        ], 200);
    }

    public function update_parent_with_scope(Request $request, $parent_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $parent_data = Parents::Where('school_branch_id', $currentSchool->id)->find($parent_id);
        if(!$parent_data){
            return response()->json([
                'status' => 'error',
                'message' => 'Parent not found'
            ], 409);
        }

        $parent_data_request = $request->all();
        $parent_data_request = array_filter($parent_data_request);
        $parent_data->fill($parent_data);
        $parent_data->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'Parent data updated succefully',
            'updated_parent' => $parent_data
        ], 201);
    }

    public function get_parent_details(Request $request){
         $currentSchool = $request->attributes->get("currentSchool");
         $parent_id = $request->route("parent_id");
         $find_parent = Parents::find($parent_id);
         if(!$find_parent){
            return response()->json([
                 "status" => "error",
                 "message" => "Parent not found"
            ], 400);
         }
        
         $parent_details = Parents::where("school_branch_id", $currentSchool->id)
                                    ->where("id", $parent_id)
                                     ->with(['student.specialty', 'student.level'])
                                     ->get();
         return response()->json([
            "status" => "ok",
            "message" => "Parent details fetched succefully",
            "parent_details" => $parent_details
         ], 200);                            
    }
    
}
