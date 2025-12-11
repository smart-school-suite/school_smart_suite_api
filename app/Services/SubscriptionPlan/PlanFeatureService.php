<?php

namespace App\Services\SubscriptionPlan;

use App\Models\Plan;
use App\Models\Feature;
use App\Models\PlanFeature;
use App\Exceptions\AppException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;

class PlanFeatureService
{
    public function assignFeatureToPlan(array $data): void
    {
        if (!isset($data['plan_id']) || !isset($data['feature_ids']) || !is_array($data['feature_ids'])) {
            throw new AppException(
                "Invalid Input Data",
                400,
                "Bad Request",
                "Invalid payload: 'plan_id' and 'feature_ids' array are required."
            );
        }

        $planId = (string) $data['plan_id'];
        $rawFeatureIds = collect($data['feature_ids']);

        if ($rawFeatureIds->isEmpty()) {
            throw new AppException(
                "No Features Provided",
                400,
                "Bad Request",
                "You must provide at least one feature to assign."
            );
        }

        $featureIds = $rawFeatureIds->pluck('feature_id')
            ->filter()
            ->map(fn($id) => (string) trim($id))
            ->unique();

        if ($featureIds->isEmpty()) {
            throw new AppException(
                "Invalid Feature IDs",
                400,
                "Bad Request",
                "No valid feature IDs were provided."
            );
        }

        DB::transaction(function () use ($planId, $featureIds) {
            $plan = Plan::select('id', 'name', 'country_id')
                ->find($planId);

            if (!$plan) {
                throw new AppException(
                    "Plan Not Found",
                    404,
                    "Resource Not Found",
                    "The plan with ID {$planId} does not exist."
                );
            }

            $invalidCountryFeatures = Feature::whereIn('id', $featureIds)
                ->where(function ($q) use ($plan) {
                    $q->whereNull('country_id')
                        ->orWhere('country_id', '!=', $plan->country_id);
                })
                ->pluck('id');

            if ($invalidCountryFeatures->isNotEmpty()) {
                throw new AppException(
                    "Country Mismatch",
                    403,
                    "Forbidden",
                    "Features " . $invalidCountryFeatures->implode(', ') . " do not belong to the same country as the plan."
                );
            }

            $alreadyAssigned = PlanFeature::where('plan_id', $plan->id)
                ->whereIn('feature_id', $featureIds)
                ->pluck('feature_id');

            if ($alreadyAssigned->isNotEmpty()) {
                throw new AppException(
                    "Duplicate Assignment",
                    409,
                    "Conflict",
                    "Features " . $alreadyAssigned->implode(', ') . " are already assigned to this plan."
                );
            }

            $insertData = $featureIds->map(function ($featureId) use ($plan) {
                return [
                    'id'         => Str::uuid()->toString(),
                    'plan_id'    => $plan->id,
                    'feature_id' => $featureId,
                    'country_id' => $plan->country_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            try {
                PlanFeature::insert($insertData);
                return $featureIds;
            } catch (QueryException $e) {
                if ($e->getCode() == '23000') {
                    throw new AppException(
                        "Integrity Constraint Violation",
                        409,
                        "Conflict",
                        "One or more features could not be assigned due to existing data."
                    );
                }
                throw new AppException(
                    "Database Error",
                    500,
                    "Internal Error",
                    "Failed to assign features."
                );
            }
        });
    }
    public function getAssignableFeatures(string $planId): Collection
    {
        $plan = Plan::select('id', 'country_id')
            ->find($planId);

        if (!$plan) {
            throw new AppException(
                "Plan Not Found",
                404,
                "Resource Not Found",
                "The plan with ID {$planId} does not exist."
            );
        }

        $assignedFeatureIds = PlanFeature::where('plan_id', $plan->id)
            ->pluck('feature_id');

        return Feature::where('country_id', $plan->country_id)
            ->when($plan->country_id === null, fn($q) => $q->whereNull('country_id'))
            ->whereNotIn('id', $assignedFeatureIds)
            ->orderBy('name')
            ->get();
    }
    public function getAssignedFeatures(string $planId): Collection
    {
        $plan = Plan::select('id', 'country_id')
            ->find($planId);

        if (!$plan) {
            throw new AppException(
                "Plan Not Found",
                404,
                "Resource Not Found",
                "The plan with ID {$planId} does not exist."
            );
        }

        return Feature::whereHas('planFeature', fn($q) => $q->where('plan_id', $plan->id))
            ->where('country_id', $plan->country_id)
            ->when($plan->country_id === null, fn($q) => $q->whereNull('country_id'))
            ->orderBy('name')
            ->get();
    }
    public function removeAssignedFeature(array $data): void
    {
        if (!isset($data['plan_id']) || !isset($data['feature_ids']) || !is_array($data['feature_ids'])) {
            throw new AppException(
                "Invalid Input Data",
                400,
                "Bad Request",
                "Both 'plan_id' and 'feature_ids' array are required."
            );
        }

        $planId = (int) $data['plan_id'];

        $featureIds = collect($data['feature_ids'])
            ->pluck('feature_id')
            ->filter()
            ->map(fn($id) => (string) trim($id))
            ->unique()
            ->values();

        if ($featureIds->isEmpty()) {
            throw new AppException(
                "No Features Provided",
                400,
                "Bad Request",
                "At least one feature ID must be provided to remove."
            );
        }

        $plan = Plan::select('id', 'name', 'country_id')
            ->find($planId);

        if (!$plan) {
            throw new AppException(
                "Plan Not Found",
                404,
                "Resource Not Found",
                "The plan with ID {$planId} does not exist."
            );
        }

        $deletedCount = PlanFeature::where('plan_id', $plan->id)
            ->whereIn('feature_id', $featureIds)
            ->delete();

        if ($deletedCount === 0) {
            throw new AppException(
                "No Features Removed",
                404,
                "Not Found",
                "None of the provided features were assigned to this plan."
            );
        }
    }
}
