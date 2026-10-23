<?php

namespace App\Services\Subscription;

use App\Exceptions\AppException;
use App\Mail\Subscription\SubscriptionSuccessfulMail;
use App\Models\Schooladmin;
use App\Models\Schoolbranches;
use App\Models\School;
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
use App\Models\PaymentMethod;
use App\Services\Helpers\Actor\ActorHelperService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class SchoolSubscriptionService
{

    public function subscribe(array $data): array
    {
        $paymentMethod = PaymentMethod::find($data['payment_method_id'] ?? null);
        if (!$paymentMethod) {
            throw new AppException(
                'Payment Method Not Found',
                400,
                'Payment Method Not Found',
                'The selected payment method is invalid or does not exist.'
            );
        }

        $adminEmail = $data['admin_email'] ?? $data['admin_mail'] ?? null;
        if (empty($adminEmail)) {
            throw new AppException(
                'Email Required',
                400,
                'Validation Error',
                'An admin email address is required.'
            );
        }

        $emailExists = Schooladmin::where('email', $adminEmail)->exists();
        if ($emailExists) {
            throw new AppException(
                'Email Already Exists',
                422,
                'Email Already Exists',
                'An admin account with this email address already exists.'
            );
        }

        $planExists = Plan::where('id', $data['plan_id'] ?? null)
            ->where('status', 'active')
            ->exists();

        if (!$planExists) {
            throw new AppException(
                'Plan Not Found',
                400,
                'Plan Not Found',
                'The selected plan does not exist or is no longer active.'
            );
        }

        return DB::transaction(function () use ($data, $paymentMethod, $adminEmail) {
            $plan = Plan::with(['country', 'planFeature'])
                ->where('id', $data['plan_id'])
                ->where('status', 'active')
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

            $school = School::create([
                'name'       => $data['school_name'],
                'country_id' => $data['country_id'],
                'type'       => $data['type'],
                'status'     => 'active',
            ]);

            $schoolId = $school->id;
            $schoolBranch = Schoolbranches::create([
                'school_id'    => $schoolId,
                'name'         => $data['school_branch_name'],
                'abbreviation' => $data['abbreviation'] ?? null,
            ]);
            $branchId = $schoolBranch->id;

            $startsAt = now();
            $endsAt   = $startsAt->clone()->addDays($plan->duration_days ?? 365);

            $subscription = SchoolSubscription::create([
                'school_branch_id' => $branchId,
                'plan_id'          => $plan->id,
                'country_id'       => $plan->country_id,
                'status'           => 'active',
                'start_date'       => $startsAt,
                'end_date'         => $endsAt,
            ]);

            $subscriptionId = $subscription->id;
            $transactionId  = (string) Str::uuid();
            $paymentRef     = $data['payment_ref'] ?? (string) Str::uuid();
            $currency       = $plan->country->currency ?? 'USD';
            $formattedAmount = number_format($plan->price, 2);

            SchoolTransaction::create([
                'school_branch_id'  => $branchId,
                'country_id'        => $plan->country_id,
                'plan_id'           => $plan->id,
                'amount'            => $plan->price,
                'currency'          => $currency,
                'type'              => 'subscription_purchase',
                'status'            => 'completed',
                'payment_method_id' => $paymentMethod->id,
                'payment_ref'       => $paymentRef,
                'transaction_id'    => $transactionId,
            ]);

            $this->createGradesCategory($branchId);
            $this->createSchoolBranchSetting($branchId);
            $this->handleCreateSubscriptionUsage($branchId, $plan->id, $subscriptionId);

            $actorHelper = app(ActorHelperService::class);

            $fullName = trim(($data['admin_first_name'] ?? '') . ' ' . ($data['admin_last_name'] ?? ''));
            if (empty($fullName)) {
                $fullName = $data['admin_full_name'] ?? 'Admin';
            }

            $username = $actorHelper->generateUsername($fullName, Schooladmin::class);

            $schoolAdmin = Schooladmin::create([
                'name'             => $data['admin_full_name'] ?? $fullName,
                'first_name'       => $data['admin_first_name'] ?? null,
                'last_name'        => $data['admin_last_name'] ?? null,
                'username'         => $username,
                'email'            => $adminEmail,
                'school_branch_id' => $branchId,
                'password'         => Hash::make($data['admin_password']),
            ]);

            $token = $schoolAdmin->createToken('schoolAdminToken')->plainTextToken;

            Mail::to($schoolAdmin->email)->queue(
                new SubscriptionSuccessfulMail(
                    schoolName: $school->name,
                    planName: $plan->name,
                    amount: $formattedAmount,
                    currency: $currency,
                    startsAt: $startsAt,
                    endsAt: $endsAt,
                    paymentReference: $paymentRef,
                    status: 'Active',
                    recipientName: $schoolAdmin->name
                )
            );

            return [
                'authToken' => $token,
            ];
        }, 3);
    }
    public function renewSubscription(array $data, object $currentSchool): array
    {
        return DB::transaction(function () use ($data, $currentSchool) {
            $paymentMethod = PaymentMethod::find($data['payment_method_id']);
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
                'payment_method_id'   => $paymentMethod->id,
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
    public function upgradePlan(array $data, object $currentSchool): array
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
    protected function createSchoolBranchSetting(string $schoolBranchId)
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
    protected function createGradesCategory(string $schoolBranchId)
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
    protected function handleCreateSubscriptionUsage(string $schoolBranchId, string $planId, string $subscriptionId)
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
    public function handleUpdateSubscriptionUsage(array $newSub, array $oldSub)
    {
        $oldUsage = SubscriptionUsage::where('school_branch_id', $oldSub['school_branch_id'])
            ->where('subscription_id', $oldSub['subscription_id'])
            ->with(['featurePlan.feature'])
            ->get();

        $oldFeatureKeys = $oldUsage->pluck('featurePlan.feature.key')->toArray();

        $newPlan = Plan::where('id', $newSub['new_plan_id'])
            ->with(['planFeature.feature'])
            ->first();

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
    public function getSchoolBranchSubscription(object $currentSchool)
    {
        return SchoolSubscription::where("school_branch_id", $currentSchool->id)
            ->with(['schoolBranch', 'plan', 'country'])->get();
    }
}
