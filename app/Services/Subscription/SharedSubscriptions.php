<?php

namespace App\Services\Subscription;

use App\Exceptions\AppException;
use App\Models\Plan;
use App\Models\SchoolSubscription;
use App\Models\SubscriptionUsage;
use Illuminate\Support\Facades\DB;

/**
 * Subscriptions belong to a school branch. A branch may share another
 * branch's subscription instead of paying for its own: it then has its own
 * subscription row (shared_from_id = the shared one) with the same plan and
 * period, and its own usage limits. The rows are kept in step when the
 * shared subscription is renewed or upgraded.
 */
class SharedSubscriptions
{
    /** The subscription that is paid for: the row itself, or the one it shares. */
    public function source(SchoolSubscription $subscription): SchoolSubscription
    {
        return $subscription->sharedFrom ?? $subscription;
    }

    public function activeFor(string $schoolBranchId): ?SchoolSubscription
    {
        return SchoolSubscription::where('school_branch_id', $schoolBranchId)
            ->where('status', 'active')
            ->where('end_date', '>=', now()->startOfDay())
            ->latest('end_date')
            ->first();
    }

    public function share(SchoolSubscription $subscription, string $schoolBranchId): SchoolSubscription
    {
        $source = $this->source($subscription);

        return DB::transaction(function () use ($source, $schoolBranchId) {
            $copy = SchoolSubscription::create([
                'school_branch_id' => $schoolBranchId,
                'plan_id' => $source->plan_id,
                'country_id' => $source->country_id,
                'status' => $source->status,
                'start_date' => $source->start_date,
                'end_date' => $source->end_date,
                'shared_from_id' => $source->id,
            ]);

            $this->syncUsage($copy);

            return $copy;
        });
    }

    /** Copies the source's plan and period to every row that shares it. */
    public function syncCopies(SchoolSubscription $source): void
    {
        $source->sharedCopies()->get()->each(function (SchoolSubscription $copy) use ($source) {
            $copy->update([
                'plan_id' => $source->plan_id,
                'country_id' => $source->country_id,
                'status' => $source->status,
                'start_date' => $source->start_date,
                'end_date' => $source->end_date,
            ]);
            $this->syncUsage($copy);
        });
    }

    /** Moves the copies of a replaced subscription (an upgrade) to its replacement. */
    public function moveCopies(SchoolSubscription $from, SchoolSubscription $to): void
    {
        SchoolSubscription::where('shared_from_id', $from->id)->update(['shared_from_id' => $to->id]);
        $this->syncCopies($to);
    }

    public function refuseChangesToCopy(SchoolSubscription $subscription): void
    {
        if (!$subscription->isShared()) {
            return;
        }

        $owner = $subscription->sharedFrom?->schoolBranch?->name ?? 'another branch';

        throw new AppException(
            'SHARED_SUBSCRIPTION',
            409,
            'Shared Subscription',
            "This branch uses {$owner}'s subscription. Renew or upgrade it from {$owner}."
        );
    }

    /** Gives the row one usage limit per feature of its plan, keeping current usage. */
    public function syncUsage(SchoolSubscription $subscription): void
    {
        $plan = Plan::with('planFeature.feature')->find($subscription->plan_id);
        if (!$plan) {
            return;
        }

        $usage = SubscriptionUsage::where('school_branch_id', $subscription->school_branch_id)
            ->where('subscription_id', $subscription->id)
            ->with('featurePlan.feature')
            ->get();

        foreach ($plan->planFeature as $feature) {
            $existing = $usage->first(fn ($row) => $row->featurePlan?->feature?->key === $feature->feature?->key);

            if ($existing) {
                $existing->update(['feature_plan_id' => $feature->id, 'limit' => $feature->value, 'limit_type' => $feature->type]);
                continue;
            }

            SubscriptionUsage::create([
                'limit' => $feature->value,
                'current_usage' => 0,
                'subscription_id' => $subscription->id,
                'feature_plan_id' => $feature->id,
                'school_branch_id' => $subscription->school_branch_id,
                'limit_type' => $feature->type,
            ]);
        }
    }
}
