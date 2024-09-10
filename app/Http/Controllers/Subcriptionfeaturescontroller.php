<?php

namespace App\Http\Controllers;

use App\Models\Subcriptionfeatures;
use Illuminate\Http\Request;

class Subcriptionfeaturescontroller extends Controller
{
    //
   public function create_subcription_feature(Request $request){
      $request->validate([
        'name' => 'required}String',
        'description' => 'required'
      ]);

      $new_subcription_feature_instance = new Subcriptionfeatures();

      $new_subcription_feature_instance->name = $request->name;
      $new_subcription_feature_instance->description = $request->description;

      $new_subcription_feature_instance->save();

      return response()->json(['message' => 'Subcription feature created succefully'], 200);

   }

   public function update_subcription_feature(Request $request, $feature_id){
       $find_new_subcription_plan = Subcriptionfeatures::find($feature_id);
       if(!$find_new_subcription_plan){
          return response()->json(['message' => 'Feature not found'], 404);
       }
       
       $fillable_data = $request->all();
       $filterd_data = array_filter($fillable_data);
       $find_new_subcription_plan->fill($filterd_data);
       $find_new_subcription_plan->save();

       return response()->json(['message' => 'subcription feature updated succefully'], 200);
   }

   public function delete_subcription_feature(Request $request, $feature_id){
    
    $find_new_subcription_plan = Subcriptionfeatures::find($feature_id);
    if(!$find_new_subcription_plan){
       return response()->json(['message' => 'Feature not found'], 404);
    }

    $find_new_subcription_plan->delete();

    return response()->json(['message' => 'Feature deleted succefully'], 200);

   }

   public function get_all_features(Request $request){
       $subcription_plan_feature = Subcriptionfeatures::all();

       return response()->json(['subcription_features' => $subcription_plan_feature], 200);
   }
}
