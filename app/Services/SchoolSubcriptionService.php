<?php

namespace App\Services;

use App\Models\SchoolSubscription;
use App\Models\RatesCard;
use App\Models\SchoolBranchApiKey;
use App\Models\SubscriptionPayment;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SchoolSubcriptionService
{
    // Implement your logic here
    public function subscribe(array $data)
    {
        try {
            $rateCard = RatesCard::findOrFail($data["rates_card_id"]);
            $totalCost = ($data["billing_frequency"] === 'monthly')
                ? $rateCard->monthly_rate_per_student * $data["num_students"]
                : $rateCard->yearly_rate_per_student * $data["num_students"];

            $subscriptionStartDate = Carbon::now();
            $subscriptionEndDate = ($data["billing_frequency"] === 'monthly')
                ? $subscriptionStartDate->copy()->addMonth()
                : $subscriptionStartDate->copy()->addYear();

                $apiKey = Str::uuid();
            DB::transaction(function () use ($data, $apiKey, $subscriptionStartDate, $subscriptionEndDate, $totalCost, $rateCard) {

                $subscription = SchoolSubscription::create([
                    'school_branch_id' => $data["school_branch_id"],
                    'rate_card_id' => $data["rates_card_id"],
                    'subscription_start_date' => $subscriptionStartDate,
                    'subscription_end_date' => $subscriptionEndDate,
                    'max_number_students' => $data["num_students"],
                    'max_number_parents' => $data["num_students"] * 2,
                    'max_number_school_admins' => $rateCard->max_school_admins,
                    'max_number_teacher' => $rateCard->max_teachers,
                    'total_monthly_cost' => $data["billing_frequency"] === 'monthly' ? $totalCost : null,
                    'total_yearly_cost' => $data["billing_frequency"] === 'yearly' ? $totalCost : null,
                    'billing_frequency' => $data["billing_frequency"],
                    'status' => 'active',
                ]);

                SchoolBranchApiKey::create([
                    'school_branch_id' => $data["school_branch_id"],
                    'api_key' => $apiKey,
                ]);
                SubscriptionPayment::create([
                    'school_subscription_id' => $subscription->id,
                    'payment_date' => $subscriptionStartDate,
                    'school_branch_id' => $data["school_branch_id"],
                    'amount' => $totalCost,
                    'payment_method' => 'card',
                    'payment_status' => 'completed',
                    'transaction_id' => Str::random(25),
                    'description' => 'Subscription payment for school ID: ' . $data["school_branch_id"]
                ]);
            });
            return $apiKey;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function subcriptionPlanDetails($subcriptionId){
        $subscription = SchoolSubscription::find($subcriptionId);
        return $subscription;
    }

    public function getAllSubcription(){
        $subscribedSchools = SchoolSubscription::all();
        return $subscribedSchools;
    }
}
