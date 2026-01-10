<?php

namespace App\Services\SubscriptionPlan;

use App\Exceptions\AppException;
use App\Models\Plan;

class PlanService
{
    public function createPlan($data)
    {
        $existingPlan = Plan::where("name", $data['name'])
            ->where("country_id", $data['country_id'])
            ->first();
        if ($existingPlan) {
            throw new AppException(
                "Plan with this name {$data['name']} already exists",
                404,
                "Duplicate Plan",
                "A plan with this {$data['name']} already exists please try another name or swap countries"
            );
        }

        $plan =  Plan::create([
            'key' => $data['key'],
            'name' => $data['name'],
            'price' => $data["price"],
            'description' => $data['description'],
            'country_id' => $data['country_id']
        ]);

        return $plan;
    }
    public function getPlanById($planId)
    {
        $plan = Plan::with(['planFeature.feature', 'country'])->find($planId);
        return $plan;
    }

    public function updatePlan($data, $planId)
    {
        $plan = Plan::find($planId);
        if (!$plan) {
            throw new AppException(
                "Plan Not Found",
                404,
                "Resource Not Found",
                "The plan with ID {$planId} does not exist."
            );
        }

        $updateData = [];
        foreach ($data as $key => $value) {
            if (!is_null($value) && $value !== '' && isset($plan->$key) && $plan->$key !== $value) {
                $updateData[$key] = $value;
            }
        }

        if (empty($updateData)) {
            throw new AppException(
                "No Valid Data For Update",
                400,
                "No Change Detected",
                "The provided data does not contain any valid new information to update Plan ID {$planId}."
            );
        }

        if (isset($updateData['name']) || isset($updateData['country_id'])) {
            $nameToCheck = $updateData['name'] ?? $plan->name;
            $countryIdToCheck = $updateData['country_id'] ?? $plan->country_id;

            $duplicatePlan = Plan::where('name', $nameToCheck)
                ->where('country_id', $countryIdToCheck)
                ->where('id', '!=', $planId)
                ->first();

            if ($duplicatePlan) {
                throw new AppException(
                    "Duplicate Plan Name and Country Combination",
                    409,
                    "Duplicate Plan",
                    "A plan with the name '{$nameToCheck}' already exists for country ID '{$countryIdToCheck}'."
                );
            }
        }


        $plan->update($updateData);

        return $plan;
    }

    public function deletePlan($planId)
    {
        $plan  = Plan::find($planId);
        if (!$plan) {
            throw new AppException(
                "Plan Not Found",
                404,
                "Resource Not Found",
                "The plan with ID {$planId} does not exist it might have been acidentally deleted please try check and try again"
            );
        }

        $plan->delete();
        return $plan;
    }

    public function getAllPlans()
    {
        $plans = Plan::all();
        if ($plans->isEmpty()) {
            throw new AppException(
                "No Plans Found",
                404,
                "No Plans Found",
                "No Plans Where Created in order to view plans you must start by creating them"
            );
        }
        return $plans;
    }

    public function getActivePlans()
    {
        $plans = Plan::where("status", "active")->get();
        if ($plans->isEmpty()) {
            throw new AppException(
                "No Plans Found",
                404,
                "No Plans Found",
                "No Active Plans Found, Looks Like Some Plans Have Been Deleted Please Verify This and try again"
            );
        }
        return $plans;
    }

    public function getActivePlansCountryId($countryId)
    {
        $plans = Plan::where("status", "active")
            ->where("country_id", $countryId)
            ->with(['planFeature.feature', 'country'])
            ->take(4)
            ->get();
        if ($plans->isEmpty()) {
            throw new AppException(
                "No Plans Found",
                404,
                "No Plans Found",
                "No Active Plans Found, Looks Like There are no active plans for this country please if error persist contact support"
            );
        }
        return $plans;
    }

    public function activatePlan($planId)
    {
        $plan = Plan::find($planId);
        if (!$plan) {
            throw new AppException(
                "Plan Not Found",
                404,
                "Resource Not Found",
                "The plan with ID {$planId} does not exist it might have been acidentally deleted please try check and try again"
            );
        }

        if ($plan->status == "active") {
            throw new AppException(
                "Plan Already Activated",
                404,
                "Plan Already Activated",
                "{$plan->name}, already activated, you can only deactivate this plan"
            );
        }

        $plan->status = "active";
        $plan->save();
        return $plan;
    }

    public function deactivatePlan($planId)
    {
        $plan = Plan::find($planId);
        if (!$plan) {
            throw new AppException(
                "Plan Not Found",
                404,
                "Resource Not Found",
                "The plan with ID {$planId} does not exist it might have been acidentally deleted please try check and try again"
            );
        }

        if ($plan->status == "inactive") {
            throw new AppException(
                "Plan Already Deactivated",
                404,
                "Plan Already Deactivated",
                "{$plan->name}, already Deactivated, you can only activate this plan"
            );
        }

        $plan->status = "inactive";
        $plan->save();
        return $plan;
    }
}
