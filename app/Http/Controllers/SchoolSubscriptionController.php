<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchoolSubscription;
use Illuminate\Support\Facades\DB;
use App\Models\SubscriptionPayment;
use App\Models\RatesCard;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SchoolSubscriptionController extends Controller
{
    //
    public function subscribe(Request $request)
    {
        $request->validate([
            'school_id' => 'required|string|exists:schools,id',
            'rates_card_id' => 'required|string|exists:rate_cards,id',
            'billing_frequency' => 'required|in:monthly,yearly',
            'num_students' => 'required|integer|min:1'
        ]);

        try {

            $rateCard = RatesCard::findOrFail($request->rates_card_id);
            $totalCost = ($request->billing_frequency === 'monthly')
                ? $rateCard->monthly_rate_per_student * $request->num_students
                : $rateCard->yearly_rate_per_student * $request->num_students;

            $subscriptionStartDate = Carbon::now();
            $subscriptionEndDate = ($request->billing_frequency === 'monthly')
                ? $subscriptionStartDate->copy()->addMonth()
                : $subscriptionStartDate->copy()->addYear();


            DB::transaction(function() use ($request, $subscriptionStartDate, $subscriptionEndDate, $totalCost, $rateCard) {


                $subscription = SchoolSubscription::create([
                    'school_id' => $request->school_id,
                    'rate_card_id' => $request->rates_card_id,
                    'subscription_start_date' => $subscriptionStartDate,
                    'subscription_end_date' => $subscriptionEndDate,
                    'max_number_students' => $request->num_students,
                    'max_number_parents' => $request->num_students * 2,
                    'max_number_school_admins' => $rateCard->max_school_admins,
                    'max_number_teacher' => $rateCard->max_teachers,
                    'total_monthly_cost' => $request->billing_frequency === 'monthly' ? $totalCost : null,
                    'total_yearly_cost' => $request->billing_frequency === 'yearly' ? $totalCost : null,
                    'billing_frequency' => $request->billing_frequency,
                    'status' => 'active',
                ]);




                SubscriptionPayment::create([
                    'school_subscription_id' => $subscription->id,
                    'payment_date' => $subscriptionStartDate,
                    'school_id' => $request->school_id,
                    'amount' => $totalCost,
                    'payment_method' => 'card',
                    'payment_status' => 'completed',
                    'transaction_id' => Str::random(25),
                    'description' => 'Subscription payment for school ID: ' . $request->school_id
                ]);
            });


            return response()->json([
                'status' => 'success',
                'message' => 'School subscription created successfully',
            ], 201);
        } catch (\Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create subscription: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function get_all_subscribed_schools(Request $request){
        $subscribed_schools = SchoolSubscription::all();
        if($subscribed_schools->isEmpty()){
            return response()->json([
               'status'=> 'error',
               'message'=> 'The records are empty'
            ], 404);
        }

        return response()->json([
            'status' => "ok",
            'message' => "Subscribed schools fetched succefully"
        ], 200);
    }

    public function subcription_details(Request $request, $subscription_id){
        $subscription = SchoolSubscription::find($subscription_id);
        if(!$subscription->isEmpty()){
             return response()->json([
                "status" => "error",
                "message" => "No records found"
             ], 404);
        }

        return response()->json([
           'status' => "ok",
           "message" => "Subscription details fetched succefully",
           "subscription_details" => $subscription
        ], 200);
    }
}
