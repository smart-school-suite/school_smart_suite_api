<?php

namespace App\Http\Controllers;

use App\Models\Subcriptionfeatures;
use Illuminate\Http\Request;

class Subcriptionfeaturescontroller extends Controller
{
    //
   public function create_subcription_feature(Request $request){
      $request->validate([
        'name' => 'required|String',
        'description' => 'required'
      ]);

      $new_subcription_feature_instance = new Subcriptionfeatures();

      $new_subcription_feature_instance->name = $request->name;
      $new_subcription_feature_instance->description = $request->description;

      $new_subcription_feature_instance->save();

      return response()->json([
         'status' => 'ok',
         'message' => 'Subcription feature created succefully',
         'created_subcription_feature' => $new_subcription_feature_instance
      ], 200);

   }

   public function update_subcription_feature(Request $request, $feature_id){
       $find_new_subcription_plan = Subcriptionfeatures::find($feature_id);
       if(!$find_new_subcription_plan){
          return response()->json([
            'status' => 'error',
            'message' => 'Feature not found'
          ], 404);
       }
       
       $fillable_data = $request->all();
       $filterd_data = array_filter($fillable_data);
       $find_new_subcription_plan->fill($filterd_data);
       $find_new_subcription_plan->save();

       return response()->json([
             'status' => 'ok',
             'message' => 'subcription feature updated succefully',
             'update_sub_feature' => $find_new_subcription_plan
       ], 200);
   }

   public function delete_subcription_feature(Request $request, $feature_id){
    
    $find_new_subcription_plan = Subcriptionfeatures::find($feature_id);
    if(!$find_new_subcription_plan){
       return response()->json([
           'status' => 'error',
           'message' => 'Feature not found'
       ], 404);
    }

    $find_new_subcription_plan->delete();

    return response()->json([
       'status' => 'ok',
       'message' => 'Feature deleted succefully',
       'deleted_plan' => $find_new_subcription_plan 
    ], 200);

   }

   public function get_all_features(Request $request){
       $subcription_plan_feature = Subcriptionfeatures::all();
        
       if($subcription_plan_feature->isEmpty()){
          return response()->json([
             'status' => 'error',
             'message' => 'no records found'
          ], 409);
       }
       return response()->json([     
         'status' => 'ok',
         'message' => 'Features fetched succefully',
         'subcription_features' => $subcription_plan_feature
       ], 200);
   }
}
