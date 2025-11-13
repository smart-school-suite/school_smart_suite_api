<?php

namespace App\Services;

use App\Models\Schoolbranches;
use App\Models\SchoolSubscription;
use App\Models\RatesCard;
use App\Models\School;
use App\Models\SchoolBranchApiKey;
use App\Models\SubscriptionPayment;
use App\Models\GradesCategory;
use App\Models\SchoolGradesConfig;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;
use Throwable;
use Illuminate\Support\Facades\DB;
use App\Models\SettingDefination;
use App\Models\SchoolBranchSetting;

class SchoolSubcriptionService
{
    // Implement your logic here
    /**
     * Handles the subscription process for a new school.
     *
     * @param array $data Contains all necessary subscription details.
     * @return array An associative array containing the generated API key.
     * @throws Exception If the subscription process fails.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the RatesCard is not found.
     */
    public function subscribe(array $data): array
    {

        if (!isset($data['rates_card_id'], $data['billing_frequency'], $data['num_students'],
                   $data['school_name'], $data['country_id'], $data['type'],
                   $data['school_branch_name'], $data['abbreviation'])) {
            throw new \InvalidArgumentException('Missing required data for subscription.');
        }

        try {

            $rateCard = RatesCard::findOrFail($data["rates_card_id"]);

            $totalCost = $this->calculateTotalCost($rateCard, $data["billing_frequency"], $data["num_students"]);

            $subscriptionStartDate = Carbon::now();
            $subscriptionEndDate = $this->determineSubscriptionEndDate($subscriptionStartDate, $data["billing_frequency"]);

            $apiKey = Str::uuid();

            DB::transaction(function () use ($data, $apiKey, $subscriptionStartDate, $subscriptionEndDate, $totalCost, $rateCard) {
                // Create School
                $schoolId = Str::uuid();
                School::create([
                    'id' => $schoolId,
                    'name' => $data['school_name'],
                    'country_id' => $data['country_id'],
                    'type' => $data["type"],
                ]);

                // Create School Branch
                $schoolBranchId = Str::uuid();
                Schoolbranches::create([
                    'id' => $schoolBranchId,
                    'name' => $data['school_branch_name'],
                    'school_id' => $schoolId,
                    'abbreviation' => $data['abbreviation'],
                ]);

                // Create School Subscription
                $subscription = SchoolSubscription::create([
                    'school_branch_id' => $schoolBranchId,
                    'rate_card_id' => $data["rates_card_id"],
                    'subscription_start_date' => $subscriptionStartDate,
                    'subscription_end_date' => $subscriptionEndDate,
                    'max_number_students' => $data["num_students"],
                    'max_number_parents' => $data["num_students"] * 2,
                    'max_number_school_admins' => $rateCard->max_school_admins,
                    'max_number_teacher' => $rateCard->max_teachers,
                    'total_monthly_cost' => ($data["billing_frequency"] === 'monthly') ? $totalCost : null,
                    'total_yearly_cost' => ($data["billing_frequency"] === 'yearly') ? $totalCost : null,
                    'billing_frequency' => $data["billing_frequency"],
                    'status' => 'active',
                ]);

                // Create School Branch API Key
                SchoolBranchApiKey::create([
                    'school_branch_id' => $schoolBranchId,
                    'api_key' => $apiKey,
                ]);

                $subscriptionId = Str::uuid();
                SubscriptionPayment::create([
                    'id' => $subscriptionId,
                    'school_subscription_id' => $subscription->id,
                    'payment_date' => $subscriptionStartDate,
                    'school_branch_id' => $schoolBranchId,
                    'amount' => $totalCost,
                    'payment_method' => 'card',
                    'payment_status' => 'completed',
                    'transaction_id' => Str::random(25),
                    'description' => "Subscription payment for school branch ID: {$schoolBranchId}",
                ]);

                $this->createGradesCategory($schoolBranchId);
                $this->createSchoolBranchSetting($schoolBranchId);

            });

            return [
                'api_key' => $apiKey,
            ];

        } catch (Throwable $e) {
            throw $e;
        }
    }

    protected function createSchoolBranchSetting($schoolBranchId){
        $settingDefs = SettingDefination::all();
         foreach($settingDefs as $settingDef){
            SchoolBranchSetting::create([
                 'school_branch_id' => $schoolBranchId,
                 'setting_defination_id' => $settingDef->id,
                 'value' => $settingDef->default_value
            ]);
         }
    }

    /**
     * Calculates the total cost of the subscription.
     */
    protected function calculateTotalCost(RatesCard $rateCard, string $billingFrequency, int $numStudents): float
    {
        if ($billingFrequency === 'monthly') {
            return $rateCard->monthly_rate_per_student * $numStudents;
        } elseif ($billingFrequency === 'yearly') {
            return $rateCard->yearly_rate_per_student * $numStudents;
        }
        // Handle invalid billing frequency - should be caught by validation earlier
        throw new \InvalidArgumentException("Invalid billing frequency: {$billingFrequency}");
    }

    /**
     * Determines the subscription end date based on frequency.
     */
    protected function determineSubscriptionEndDate(Carbon $startDate, string $billingFrequency): Carbon
    {
        if ($billingFrequency === 'monthly') {
            return $startDate->copy()->addMonth();
        } elseif ($billingFrequency === 'yearly') {
            return $startDate->copy()->addYear();
        }
        // Handle invalid billing frequency - should be caught by validation earlier
        throw new \InvalidArgumentException("Invalid billing frequency: {$billingFrequency}");
    }

    public function subcriptionPlanDetails($subcriptionId){
        $subscription = SchoolSubscription::find($subcriptionId);
        return $subscription;
    }

    public function getAllSubcription(){
        $subscribedSchools = SchoolSubscription::all();
        return $subscribedSchools;
    }

    protected function createGradesCategory($schoolBranchId){
        $gradeCategories = GradesCategory::all();
            $configs = $gradeCategories->map(function ($gradeCategory) use ($schoolBranchId) {
                return [
                    'id' => Str::uuid(),
                    'school_branch_id' => $schoolBranchId,
                    'grades_category_id' => $gradeCategory->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            SchoolGradesConfig::insert($configs);
    }
}
