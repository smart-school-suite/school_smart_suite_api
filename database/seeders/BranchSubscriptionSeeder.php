<?php

namespace Database\Seeders;

use App\Models\SchoolSubscription;
use App\Models\Schoolbranches;
use App\Services\Subscription\SharedSubscriptions;
use Illuminate\Database\Seeder;

/**
 * Subscriptions belong to a school branch. A branch that has none yet,
 * while another branch of its school has an active subscription, is given
 * a shared copy of it. Branches that already have a subscription (even an
 * expired one) are left alone, so running this again changes nothing.
 */
class BranchSubscriptionSeeder extends Seeder
{
    public function run(SharedSubscriptions $shared): void
    {
        $linked = 0;

        Schoolbranches::query()
            ->whereDoesntHave('schoolSubscription')
            ->get(['id', 'school_id', 'name'])
            ->each(function (Schoolbranches $branch) use ($shared, &$linked) {
                $source = SchoolSubscription::query()
                    ->whereNull('shared_from_id')
                    ->where('status', 'active')
                    ->where('end_date', '>=', now()->startOfDay())
                    ->whereHas('schoolBranch', fn ($query) => $query->where('school_id', $branch->school_id))
                    ->latest('end_date')
                    ->first();

                if ($source) {
                    $shared->share($source, $branch->id);
                    $linked++;
                }
            });

        if ($linked) {
            $this->command?->info("Linked {$linked} branch(es) to their school's subscription.");
        }
    }
}
