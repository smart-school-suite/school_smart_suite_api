<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanRecommendation;
use App\Models\PlanRecCondition;
use App\Models\PlanRecCopy;
use Illuminate\Support\Str;

class PlanRecommendationSeeder extends Seeder
{
    public function run(): void
    {
        $plans = Plan::where("max_plan", false)->get();

        $features = [
            ["key" => "timetable.generation", "priority" => 7, "name" => "Timetable Generations"],
            ["key" => "school.admins", "priority" => 5, "name" => "School Admins"],
            ["key" => "rate.limit", "priority" => 6, "name" => "Rate Limit"],
            ["key" => "school.announcements", "priority" => 5, "name" => "School Announcements"],
            ["key" => "school.events", "priority" => 3, "name" => "School Events"],
            ["key" => "school.elections", "priority" => 5, "name" => "School Elections"],
            ["key" => "school.departments", "priority" => 5, "name" => "Departments"],
            ["key" => "school.specialties", "priority" => 2, "name" => "Specialties"],
            ["key" => "school.courses", "priority" => 6, "name" => "Courses"],
        ];

        $featuresByKey = Feature::whereIn('key', array_column($features, 'key'))
            ->get()
            ->keyBy('key');

        foreach ($plans as $plan) {
            $targetPlan = Plan::where('price', '>', $plan->price)
                ->where('country_id', $plan->country_id)
                ->orderBy('price', 'asc')
                ->first();

            if (!$targetPlan) {
                continue;
            }

            foreach ($features as $featureData) {
                $feature = $featuresByKey->get($featureData['key']);

                if (!$feature) {
                    continue;
                }

                $recommendation = PlanRecommendation::updateOrCreate(
                    [
                        'source_plan_id' => $plan->id,
                        'target_plan_id' => $targetPlan->id,
                        'feature_id' => $feature->id,
                    ],
                    [
                        'id' => PlanRecommendation::where('source_plan_id', $plan->id)
                            ->where('target_plan_id', $targetPlan->id)
                            ->where('feature_id', $feature->id)
                            ->value('id') ?? Str::uuid()->toString(),
                        'priority' => $featureData['priority'],
                        'status' => 'active',
                    ]
                );

                $condition = PlanRecCondition::updateOrCreate(
                    [
                        'plan_rec_id' => $recommendation->id,
                        'operator' => 'percentage',
                    ],
                    [
                        'id' => PlanRecCondition::where('plan_rec_id', $recommendation->id)
                            ->where('operator', 'percentage')
                            ->value('id') ?? Str::uuid()->toString(),
                        'value' => json_encode(['threshold' => 0.7]),
                    ]
                );

                PlanRecCopy::updateOrCreate(
                    [
                        'plan_rec_cond_id' => $condition->id,
                    ],
                    [
                        'title' => "You're reaching your {$featureData['name']} limit",
                        'description' => "Your current plan allows a limited {$featureData['name']}. Upgrade to the {$targetPlan->name} plan to increase this limit and continue uninterrupted.",
                        'cta_text' => "Upgrade to {$targetPlan->name}",
                    ]
                );
            }
        }
    }
}
