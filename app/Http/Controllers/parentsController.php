<?php

namespace App\Http\Controllers;

use App\Models\Parents;
use Illuminate\Http\Request;

class parentsController extends Controller
{
    public function get_all_parents_within_a_School_without_relations(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $parents = Parents::Where('school_id', $currentSchool->id)->get();
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

    
}
