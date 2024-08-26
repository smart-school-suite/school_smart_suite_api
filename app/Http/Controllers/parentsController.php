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

    public function get_all_parents_without_relations_without_teenant_scope(Request $request){
        $parents = Parents::all();
        return response()->json(['parent' => $parents], 200);
    }

    public function get_all_parents_with_relations_without_scope(Request $request){
        $parents = Parents::with('student');
        return response()->json(['parent' => $parents], 200);
    }
    
    public function delete_parent_with_scope(Request $request, $parent_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $parent_data = Parents::Where('school_branch_id', $currentSchool->id)->find($parent_id);
        if(!$parent_data){
            return response()->json(['message' => 'Parent not found'], 409);
        }

        $parent_data->delete();

        return response()->json(['message' => 'Parent deleted sucessfully'], 200);
    }

    public function update_parent_with_scope(Request $request, $parent_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $parent_data = Parents::Where('school_branch_id', $currentSchool->id)->find($parent_id);
        if(!$parent_data){
            return response()->json(['message' => 'Parent not found'], 409);
        }

        $parent_data_request = $request->all();
        $parent_data_request = array_filter($parent_data_request);
        $parent_data->fill($parent_data);

        return response()->json(['message' => 'Parent data updated succefully'], 201);
    }

    
    
}
