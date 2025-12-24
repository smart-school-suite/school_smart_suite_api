<?php

namespace App\Services\Subscription;

use App\Exceptions\AppException;
use App\Models\Schoolbranches;
use App\Models\School;
use App\Models\SchoolBranchApiKey;
use App\Models\GradesCategory;
use App\Models\Plan;
use App\Models\SchoolGradesConfig;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\SettingDefination;
use App\Models\SchoolBranchSetting;
use App\Models\SchoolTransaction;
use App\Models\SchoolSubscription;
use App\Models\SubscriptionUsage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SchoolSubscriptionService
{

    public function subscribe(array $data): array
    {
        $plan = Plan::with(['country', 'planFeature'])
            ->where('id', $data['plan_id'])
            ->where("status", "active")
            ->lockForUpdate()
            ->first();

        if (!$plan) {
            throw new AppException(
                'Plan Not Found',
                400,
                'Plan Not Found',
                'The selected plan does not exist or is no longer active.'
            );
        }

        return DB::transaction(function () use ($data, $plan) {
            $schoolId     = (string) Str::uuid();
            $branchId     = (string) Str::uuid();
            $rawApiKey    = (string) Str::uuid();
            $subscriptionId = (string) Str::uuid();
            School::create([
                'id'         => $schoolId,
                'name'       => $data['school_name'],
                'country_id' => $data['country_id'],
                'type'       => $data['type'],
                'status'     => 'active',
            ]);

            Schoolbranches::create([
                'id'           => $branchId,
                'school_id'    => $schoolId,
                'name'         => $data['school_branch_name'],
                'abbreviation' => $data['abbreviation'] ?? null,
            ]);

            $startsAt = now();
            $endsAt   = $startsAt->clone()->addDays($plan->duration_days ?? 365);

            SchoolSubscription::create([
                'id' => $subscriptionId,
                'school_branch_id' => $branchId,
                'plan_id'          => $plan->id,
                'country_id'       => $plan->country_id,
                'status'           => 'active',
                'start_date'       => $startsAt,
                'end_date'         => $endsAt,
            ]);

            SchoolBranchApiKey::create([
                'school_branch_id' => $branchId,
                'api_key'          => $rawApiKey,
                'issued_at'        => now(),
            ]);

            $transactionId = (string) Str::uuid();

            SchoolTransaction::create([
                'id'               => $transactionId,
                'school_branch_id' => $branchId,
                'country_id'       => $plan->country_id,
                'plan_id'          => $plan->id,
                'amount'           => $plan->price,
                'currency'         => $plan->country->currency ?? 'USD',
                'type'             => 'subscription_purchase',
                'status'           => 'completed',
                'payment_method'   => $data['payment_method'] ?? 'mtn_mobile_money',
                'payment_ref'      => $data['payment_ref'] ?? (string) Str::uuid(),
                'transaction_id'   => $transactionId,
            ]);

            $this->createGradesCategory($branchId);
            $this->createSchoolBranchSetting($branchId);
            $this->handleCreateSubscriptionUsage($branchId, $plan->id, $subscriptionId);
            return [
                'api_key'    => $rawApiKey,
                'school_id'  => $schoolId,
                'branch_id'  => $branchId
            ];
        }, 3);
    }
    public function renewSubscription(array $data, $currentSchool): array
    {
        return DB::transaction(function () use ($data, $currentSchool) {
            $branch = Schoolbranches::with([
                'schoolSubscription.plan.country'
            ])
                ->where('id', $currentSchool->id)
                ->whereHas('schoolSubscription')
                ->lockForUpdate()
                ->firstOrFail();

            $subscription = $branch->schoolSubscription;

            $plan = Plan::with('country')
                ->where('id', $data['plan_id'] ?? $subscription->plan_id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->firstOrFail();

            $startsAt = $subscription->end_date->isFuture()
                ? $subscription->end_date
                : now();

            $endsAt = $startsAt->clone()->addDays($plan->duration_days ?? 365);

            $subscription->update([
                'plan_id'     => $plan->id,
                'country_id'  => $plan->country_id,
                'status'      => 'active',
                'start_date'  => $startsAt,
                'end_date'    => $endsAt,
                //'auto_renew'  => $data['auto_renew'] ?? $subscription->auto_renew,
            ]);

            $transactionId = (string) Str::uuid();

            SchoolTransaction::create([
                'id'               => $transactionId,
                'school_branch_id' => $branch->id,
                'country_id'       => $plan->country_id,
                'plan_id'          => $plan->id,
                'subscription_id'  => $subscription->id,
                'amount'           => $plan->price,
                'currency'         => $plan->country->currency ?? 'USD',
                'type'             => 'subscription_renewal',
                'status'           => 'completed',
                'payment_method'   => $data['payment_method'] ?? 'mtn_mobile_money',
                'payment_ref'      => $data['payment_ref'] ?? (string) Str::uuid(),
            ]);

            return [
                'subscription_id' => $subscription->id,
                'plan_id'         => $plan->id,
                'start_date'      => $startsAt->toDateString(),
                'end_date'        => $endsAt->toDateString(),
                'expires_at'      => $endsAt->toDateString(),
            ];
        }, 3);
    }
    public function upgradePlan(array $data, $currentSchool): array
    {
        return DB::transaction(function () use ($data, $currentSchool) {
            $branchId = $currentSchool->id;

            $branch = Schoolbranches::where('id', $branchId)
                ->lockForUpdate()
                ->firstOrFail();

            $now = now();

            $currentSubscription = SchoolSubscription::where('school_branch_id', $branchId)
                ->where('status', 'active')
                ->where('start_date', '<=', $now)
                ->where(function ($q) use ($now) {
                    $q->whereNull('end_date')
                        ->orWhere('end_date', '>=', $now);
                })
                ->lockForUpdate()
                ->latest('start_date')
                ->first();

            if (!$currentSubscription) {
                throw new AppException('NO_ACTIVE_SUBSCRIPTION', 400, 'Upgrade failed', 'No active subscription found to upgrade.');
            }

            $newPlan = Plan::where('id', $data['new_plan_id'])
                ->where('status', 'active')
                ->lockForUpdate()
                ->firstOrFail();

            if ($currentSubscription->plan_id === $newPlan->id) {
                throw new AppException('SAME_PLAN', 400, 'Invalid Upgrade', 'You are already on this plan.');
            }

            $currentPlanPrice = (float) $currentSubscription->plan->price;
            $newPlanPrice     = (float) $newPlan->price;

            if ($newPlanPrice < $currentPlanPrice) {
                throw new AppException('DOWNGRADE_NOT_ALLOWED', 400, 'Invalid Upgrade', 'Cannot upgrade to a cheaper plan.');
            }

            $newEnd = $now->copy()->addDays($newPlan->duration_days ?? 365);

            $currentSubscription->update([
                'status'   => 'cancelled',
                'end_date' => $now,
            ]);

            $newSubscriptionId = (string) Str::uuid();

            $newSubscription = SchoolSubscription::create([
                'id'               => $newSubscriptionId,
                'school_branch_id' => $branch->id,
                'plan_id'          => $newPlan->id,
                'country_id'       => $newPlan->country_id ?? $branch->country_id ?? null,
                'status'           => 'active',
                'start_date'       => $now,
                'end_date'         => $newEnd,
            ]);

            $transactionId = (string) Str::uuid();

            $transaction = SchoolTransaction::create([
                'id'               => $transactionId,
                'school_branch_id' => $branch->id,
                'country_id'       => $newPlan->country_id,
                'plan_id'          => $newPlan->id,
                'subscription_id'  => $newSubscription->id,
                'amount'           => $newPlanPrice,
                'currency'         => $newPlan->country->currency ?? 'XAF',
                'type'             => 'subscription_upgrade',
                'status'           => 'completed',
                'payment_method'   => $data['payment_method'] ?? 'mtn_mobile_money',
                'payment_ref'      => $data['payment_ref'] ?? (string) Str::uuid(),
                'transaction_id'   => $transactionId,
            ]);

            $oldSub = [
                "school_branch_id" => $branchId,
                "subscription_id"  => $currentSubscription->id,
            ];
            $newSub = [
                "new_plan_id" => $data['new_plan_id'],
                "new_subscription_id" => $newSubscriptionId
            ];

            $this->handleUpdateSubscriptionUsage($newSub, $oldSub);

            return [
                'transaction'          => $transaction,
                'subscription'         => $newSubscription,
                'previous_subscription' => $currentSubscription->fresh(),
            ];
        }, 3);
    }
    protected function createSchoolBranchSetting($schoolBranchId)
    {
        $settingDefs = SettingDefination::all();
        foreach ($settingDefs as $settingDef) {
            SchoolBranchSetting::create([
                'school_branch_id' => $schoolBranchId,
                'setting_defination_id' => $settingDef->id,
                'value' => $settingDef->default_value
            ]);
        }
    }
    protected function createGradesCategory($schoolBranchId)
    {
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
    protected function handleCreateSubscriptionUsage($schoolBranchId, $planId, $subscriptionId)
    {
        $plan = Plan::where("id", $planId)
            ->with(['planFeature.feature'])
            ->first();
        $planFeatures = $plan->planFeature;
        foreach ($planFeatures as $planFeature) {
            SubscriptionUsage::create([
                'limit' => $planFeature->value,
                'current_usage' => 0,
                'subscription_id' => $subscriptionId,
                'feature_plan_id' => $planFeature->id,
                'school_branch_id' => $schoolBranchId,
                "limit_type" => $planFeature->type
            ]);
        }
    }
    public function handleUpdateSubscriptionUsage($newSub, $oldSub)
    {
        $oldUsage = SubscriptionUsage::where('school_branch_id', $oldSub['school_branch_id'])
            ->where('subscription_id', $oldSub['subscription_id'])
            ->with(['featurePlan.feature'])
            ->get();

        $oldFeatureKeys = $oldUsage->pluck('featurePlan.feature.key')->toArray();

        $newPlan = Plan::where('id', $newSub['new_plan_id'])
            ->with(['planFeature.feature'])
            ->first();

      Log::info($newPlan);
        $newPlanFeatures = $newPlan->planFeature;

        $newFeatureKeys = $newPlanFeatures->pluck('feature.key')->toArray();

        foreach ($newPlanFeatures as $feat) {
            $key = $feat->feature->key;

            $existing = $oldUsage->firstWhere('featurePlan.feature.key', $key);

            if (!$existing) {
                SubscriptionUsage::create([
                    'limit' => $feat->value,
                    'current_usage' => 0,
                    'subscription_id' => $newSub['new_subscription_id'],
                    'feature_plan_id' => $feat->id,
                    'school_branch_id' => $oldSub['school_branch_id'],
                    'limit_type' => $feat->type,
                ]);
            } else {
                $existing->update([
                    'subscription_id' => $newSub['new_subscription_id'],
                    'feature_plan_id' => $feat->id,
                    'limit' => $feat->value,
                ]);
            }
        }
    }
    public function getSchoolBranchSubscription($currentSchool)
    {
        return SchoolSubscription::where("school_branch_id", $currentSchool->id)
            ->with(['schoolBranch', 'plan', 'country'])->get();
    }
}
