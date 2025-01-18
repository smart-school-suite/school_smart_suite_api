<?php

namespace App\Http\Controllers;
use App\Models\School;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class schoolsController extends Controller
{
    //

    public function register_school_to_edumanage(Request $request)
{
    // Validate the request input
    $request->validate([
        'country_id' => 'required|string',
        'name' => 'required|string',
        'address' => 'required|string',
        'city' => 'required|string',
        'state' => 'required|string',
        'MAX_GPA' => 'required',
        'motor' => 'required',
        'type' => 'required|string',
        'established_year' => 'nullable|string',
        'director_name' => 'required|string',
    ]);


    $new_school_instance = new School();

    $random_id = Str::uuid()->toString();
    $new_school_instance->id = $random_id;

    $new_school_instance->country_id = $request->country_id;
    $new_school_instance->name = $request->name;
    $new_school_instance->address = $request->address;
    $new_school_instance->city = $request->city;
    $new_school_instance->state = $request->state;
    $new_school_instance->MAX_GPA = $request->MAX_GPA;
    $new_school_instance->motor = $request->motor;
    $new_school_instance->type = $request->type;
    $new_school_instance->established_year = $request->established_year;
    $new_school_instance->director_name = $request->director_name;

    $new_school_instance->save();


    return response()->json([
        'status' => 'ok',
        'message' => 'School created successfully',
        'created_School' => $new_school_instance,
        'school_key' =>  $random_id,
    ], 201);
}


    public function update_school(Request $request, $school_id){
        $school = School::find($school_id);
        if(!$school){
            return response()->json([
                'status' => 'ok',
                'message' => 'school not found'
            ], 409);
        }

        $school_data = $request->all();
        $school_data =  array_filter($school_data);
        $school->fill($school_data);
        $school->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'school updated succesfully',
            'updated_school' => $school
        ], 200);

    }

    public function delete_school(Request $request, $school_id){
        $school = School::find($school_id);
        if($school){
            return response()->json([
                'status' => 'ok',
                'message' => 'school not found'
            ], 409);
        }

        $school->delete();

        return response()->json([
            'status' => 'ok',
            'message' => 'school deleted succesfully',
            'created_School' => $school
        ], 200);
    }

    public function get_all_schools(Request $request){
        $school = School::all();
        if($school->isEmpty()){
            return response()->json([
                'status' => 'ok',
                'message' => 'no records found'
            ], 409);
        }
        return response()->json([
            'status' => 'ok',
            'message' => 'schools records fetched sucessfully',
            'schools_data' => $school
        ], 200);
    }

    public function get_schools_with_branches(Request $request){
        $school = School::with('schoolbranches')->get();
        return response()->json(['school_data' => $school], 200);
    }

}
