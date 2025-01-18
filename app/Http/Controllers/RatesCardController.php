<?php

namespace App\Http\Controllers;

use App\Models\RatesCard;
use Illuminate\Http\Request;

class RatesCardController extends Controller
{
    public function create_rates(Request $request)
    {
        $request->validate([
            'min_students' => 'required|integer',
            'max_students' => 'required|integer',
            'monthly_rate_per_student' => 'required|numeric',
            'yearly_rate_per_student' => 'required|numeric',
            'subscription_plan_id' => 'required|string|exists:subscription_plans,id',
        ]);

        $rateCard = RatesCard::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Rate card created successfully',
            'data' => $rateCard
        ],200);
    }

    public function update_rates_card(Request $request, $rates_id){
         $find_rate = RatesCard::find($rates_id);
         if(!$find_rate){
            return response()->json([
                'status' => "error",
                "message" => "Rates not found"
            ], 404);
         }

         $request->validate([
            'min_students' => 'sometimes|integer',
            'max_students' => 'sometimes|integer',
            'monthly_rate_per_student' => 'sometimes|numeric',
            'yearly_rate_per_student' => 'sometimes|numeric',
            'subscription_plan_id' => 'sometimes|string|exists:subscription_plans,id',
        ]);

        $update_data = $request->all();
        $clean_rates_data = array_filter($update_data);

        $find_rate->update($clean_rates_data);

        return response()->json([
           'status' => "ok",
           "message" => "rates updated succeefully"
        ], 200);
    }

    public function delete_rate(Request $request, $rate_id){
        $find_rate = RatesCard::find($rate_id);
        if(!$find_rate){
             return response()->json([
                 'status' => "error",
                 "message" => "Rate not found"
             ], 404);
        }

        $find_rate->delete();

        return response()->json([
             'status' => 'ok',
             "message" => "Rate deleted succefully"
        ], 200);
    }

    public function get_rates(Request $request){
        $rates = RatesCard::all();
        if($rates->isEmpty()){
             return response()->json([
                 'status' => "error",
                 "message" => "No records found"
             ], 404);
        }

        return response()->json([
             'status' => "ok",
             "message"=> "Rates fetched sucessfully",
             "rates" => $rates
        ], 200);
    }
}
