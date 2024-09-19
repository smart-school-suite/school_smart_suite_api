<?php

namespace App\Http\Controllers;
use App\Models\Schoolbranches;
use Illuminate\Http\Request;

class schoolbranchesController extends Controller
{
    //

    public function create_school_branch(Request $request){
         $request->validate([
            'school_id' => 'required|string',
            'branch_name' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'postal_code' => 'required|string',
            'website' => 'string',
            'phone_one' => 'required|string',
            'phone_two' => 'required|string',
            'email' => 'required|email|string',
         ]);

         $new_school_branch_instance = new Schoolbranches();

         $new_school_branch_instance->school_id = $request->school_id;
         $new_school_branch_instance->branch_name = $request->branch_name;
         $new_school_branch_instance->address = $request->address;
         $new_school_branch_instance->city = $request->city;
         $new_school_branch_instance->state = $request->state;
         $new_school_branch_instance->postal_code = $request->postal_code;
         $new_school_branch_instance->phone_two = $request->phone_two;
         $new_school_branch_instance->phone_one = $request->phone_one;
         $new_school_branch_instance->website = $request->website;
         $new_school_branch_instance->email = $request->email;

         $new_school_branch_instance->save();

         return response()->json(['message' => 'School branch created succesfully'], 200);
    }

    public function update_school_branch(Request $request, $branch_id){
        $school_branch = Schoolbranches::find($branch_id);
        if(!$school_branch){
            return response()->json(['message' => 'school branch not found'], 404);
        }

        $school_branch_data = $request->all();
        $school_branch_data = array_filter($school_branch_data);
        $school_branch->fill();
        
        $school_branch->save();


        return response()->json(['message' => 'school branch updated successfully'], 200);
    }

    public function get_all_schoool_branches(Request $request){
        $school_branch_data = Schoolbranches::all();
        return response()->json(['school_branch_data' => $school_branch_data], 200);
    }

    public function delete_school_branch(Request $request, $branch_id){
        $school_branch = Schoolbranches::find($branch_id);
        if(!$school_branch){
            return response()->json(['message' => 'school branch not found'], 404);
        }
       
        $school_branch->delete();

        return response()->json(['message' => 'School branch deleted sucessfully'], 200);
    }

    public function get_all_school_branches_scoped(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $school_branch = Schoolbranches::Where('school_id', $currentSchool->id)->get();
        return response()->json(['school_branch' => $school_branch], 200);
    }

    public function get_all_school_branches_with_relations(Request $request){
        $school_data = Schoolbranches::with('school');
        return response()->json(['school_data' => $school_data], 200);
    }

    
}
