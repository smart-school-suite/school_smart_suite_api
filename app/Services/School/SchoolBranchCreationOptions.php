<?php

namespace App\Services\School;

use App\Models\SchoolSubscription;
use App\Models\Schoolbranches;
use App\Services\Subscription\SharedSubscriptions;

/** What the Create Branch drawer needs: the school, its branch names and the subscription it can share. */
class SchoolBranchCreationOptions
{
    public function __construct(private SharedSubscriptions $shared) {}

    public function describe(object $currentSchool): array
    {
        $current = Schoolbranches::with(['school.country'])->findOrFail($currentSchool->id);
        $school = $current->school;
        $active = $this->shared->activeFor($current->id);
        $source = $active ? $this->shared->source($active)->load(['plan', 'schoolBranch']) : null;

        return [
            'school' => [
                'id' => $school->id,
                'name' => $school->name,
                'country_id' => $school->country_id,
                'currency' => $school->country?->currency,
            ],
            'current_branch' => ['id' => $current->id, 'name' => $current->name],
            'branch_names' => Schoolbranches::where('school_id', $school->id)->orderBy('name')->pluck('name')->all(),
            'shared' => $source ? [
                'available' => true,
                'plan' => ['id' => $source->plan?->id, 'name' => $source->plan?->name],
                'end_date' => date('Y-m-d', strtotime($source->end_date)),
                'days_left' => max(0, (int) now()->startOfDay()->diffInDays(date('Y-m-d', strtotime($source->end_date)), false)),
                'owner' => $source->schoolBranch?->name,
                'branches' => SchoolSubscription::with('schoolBranch:id,name')
                    ->where(fn ($query) => $query->where('id', $source->id)->orWhere('shared_from_id', $source->id))
                    ->get()
                    ->pluck('schoolBranch.name')
                    ->filter()
                    ->values()
                    ->all(),
            ] : [
                'available' => false,
                'reason' => "{$current->name} has no active subscription to share.",
            ],
        ];
    }
}
