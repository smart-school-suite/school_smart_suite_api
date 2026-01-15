<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        // Fetch all plans (excluding max_plan)
        $plans = Plan::where("max_plan", false)->get();

        // Define features
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

        // Loop through plans
        foreach ($plans as $plan) {
            foreach ($features as $featureData) {

                // Determine next plan to recommend (for simplicity, next higher priced plan)
                $targetPlan = Plan::where('price', '>', $plan->price)
                    ->where('country_id', $plan->country_id)
                    ->orderBy('price', 'asc')
                    ->first();

                if (!$targetPlan) {
                    continue; // no higher plan to recommend
                }

                // Create Plan Recommendation
                $recommendationId = Str::uuid();
                PlanRecommendation::create([
                    'id' => $recommendationId,
                    'source_plan_id' => $plan->id,
                    'target_plan_id' => $targetPlan->id,
                    'feature_id' => Feature::where('key', $featureData['key'])->first()->id,
                    'priority' => $featureData['priority'],
                    'status' => 'active',
                ]);

                // Create Recommendation Condition (example: 80% usage triggers)
                $planRecConId = Str::uuid();
                PlanRecCondition::create([
                    'id' => $planRecConId,
                    'plan_rec_id' => $recommendationId,
                    'operator' => 'percentage',
                    'value' => json_encode(['threshold' => 0.7]), // 70% usage
                ]);

                // Create Recommendation Copy (UI friendly)

                PlanRecCopy::create([
                    'plan_rec_cond_id' => $planRecConId,
                    'title' => "You're reaching your {$featureData['name']} limit",
                    'description' => "Your current plan allows a limited {$featureData['name']}. Upgrade to the {$targetPlan->name} plan to increase this limit and continue uninterrupted.",
                    'cta_text' => "Upgrade to {$targetPlan->name}",
                ]);
            }
        }
    }
}
