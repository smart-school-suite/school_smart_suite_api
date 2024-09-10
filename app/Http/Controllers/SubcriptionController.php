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
            'max_number_students' => 'required|string',
            'monthly_price' => 'required|decimal:min,max',
            'yearly_price' => 'required|decimal:min,max',
            'description_id' => 'required|string'
        ]);

        $new_subcription_instance = new Subcription();

        $new_subcription_instance->name = $request->name;
        $new_subcription_instance->max_number_students = $request->max_number_students;
        $new_subcription_instance->monthly_price = $request->monthly_price;
        $new_subcription_instance->yearly_price = $request->yearly_price;

        $new_subcription_instance->save();

        return response()->json(['message' => 'subcription plan created succefully'], 200);
    }

    public function update_subcription(Request $request, $subcription_id){
        $find_subcription = Subcription::find($subcription_id);
        
        if(!$find_subcription){
            return response()->json(['message' => 'Subcription plan not found'], 404);
        }

        $fillable_data = $request->all();
        $filtered_data = array_filter($fillable_data);
        $find_subcription->fill($filtered_data);
        $find_subcription->save();

        return response()->json(['message' => 'Subcription updated succefully'], 200);
    }

    public function delete_subcription(Request $request, $subcription_id){
        $find_subcription = Subcription::find($subcription_id);
        
        if(!$find_subcription){
            return response()->json(['message' => 'Subcription plan not found'], 404);
        }

        $find_subcription->delete();

        return response()->json(['message' => 'subcription plan deleted sucessfully'], 200);
    }

    public function get_all_subcription_plans(Request $request){
        $subcription_data = Subcription::with(['subfeatures'])->get();
        return response()->json(['subcription_data' => $subcription_data], 200);
    }
}
