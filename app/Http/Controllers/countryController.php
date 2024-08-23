<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;

class countryController extends Controller
{
    //
    public function create_country(Request $request){
         $request->validate([
           'country' => 'required|string'
         ]);

         $country = new Country();

         $country->country = $request->country;
         
         $country->save();

         return response()->json(['message' => 'country created sucessfully'], 200);
    }

    public function update_country(Request $request, $country_id){
         $country = Country::find($country_id);
         if(!$country){
             return response()->json(['message' => 'could not find country'], 404);
         }

         $country_data = $request->all();
         $country_data = array_filter($country_data);
         $country->fill();

         $country->save();

         return response()->json(['message' => 'country updated succesfully'], 200);
    }

    public function delete_country(Request $request, $country_id){
        $country = Country::find($country_id);
        if(!$country){
            return response()->json(['message' => 'could not find country'], 404);
        }

        $country->delete();

        return response()->json(['message' => 'country deleted succesfully'], 200);
    }

    public function get_all_countries(Request $request){
        $country = Country::all();
        
        return response()->json(['countries' => $country], 200);
    }

    public function get_all_countries_with_all_relations(Request $request){
        $country_data_with_relations = Country::with('school');
        return response()->json(['country_data' => $country_data_with_relations], 200);
    }
}
