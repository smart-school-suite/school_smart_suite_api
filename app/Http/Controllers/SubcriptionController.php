<?php

namespace App\Http\Controllers;

use App\Models\Subcription;
use Illuminate\Http\Request;

class SubcriptionController extends Controller
{
    //
    public function create_subcription(Request $request){
        $request->validate([
            'name' => 'required|string',
            'max_number_students' => 'required|integer',
            'monthly_price' => 'required',
            'yearly_price' => 'required',
            'description_id' => 'required|string'
        ]);

        $new_subcription_instance = new Subcription();

        $new_subcription_instance->name = $request->name;
        $new_subcription_instance->max_number_students = $request->max_number_students;
        $new_subcription_instance->monthly_price = $request->monthly_price;
        $new_subcription_instance->yearly_price = $request->yearly_price;
        $new_subcription_instance->description_id = $request->description_id;

        $new_subcription_instance->save();

        return response()->json([
            'status' => 'error',
            'message' => 'records created succesfully',
            'created_data' => $new_subcription_instance
        ], 200);
    }

    public function update_subcription(Request $request, $subcription_id){
        $find_subcription = Subcription::find($subcription_id);
        
        if(!$find_subcription){
            return response()->json([
                'status' => 'error',
                'message' => 'Subcription plan not found'
            ], 409);
        }

        $fillable_data = $request->all();
        $filtered_data = array_filter($fillable_data);
        $find_subcription->fill($filtered_data);
        $find_subcription->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'Subcription updated succefully'
        ], 200);
    }

    public function delete_subcription(Request $request, $subcription_id){
        $find_subcription = Subcription::find($subcription_id);
        
        if(!$find_subcription){
            return response()->json([
                'status' => 'error',
                'message' => 'Subcription plan not found'
            ], 404);
        }

        $find_subcription->delete();

        return response()->json([
            'status' => 'ok',
            'message' => 'subcription plan deleted sucessfully',
            'deleted_subcription' => $find_subcription
        ], 200);
    }

    public function get_all_subcription_plans(Request $request){
        $subcription_data = Subcription::with(['subfeatures'])->get();
        if($subcription_data->isEmpty()){
            return response()->json([
                'status' => 'ok',
                'message' => 'No records found'
            ], 409);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Subcription created succefully',
            'subcription_data' => $subcription_data
        ], 200);
    }
}
