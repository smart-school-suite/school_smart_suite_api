<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PlanFeature;
use App\Models\Plan;
use App\Models\Feature;

class FeaturePlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limits climb with each plan so every upgrade buys real headroom.
        // -1 means unlimited.
        $ladder = [
            //                         starter growth professional enterprise ultimate
            "timetable.generation" => [10, 30, 80, 200, -1],
            "school.admins"        => [3, 8, 20, 50, -1],
            "rate.limit"           => [60, 120, 300, 600, 1200],
            "school.announcements" => [50, 200, 600, 2000, -1],
            "school.events"        => [20, 60, 150, 400, -1],
            "school.elections"     => [2, 5, 15, 40, -1],
            "school.departments"   => [5, 12, 30, 80, -1],
            "school.specialties"   => [10, 30, 80, 200, -1],
            "school.courses"       => [60, 200, 600, 1500, -1],
        ];
        $planKeys = ["starter.plan", "growth.plan", "professional.plan", "enterpise.plan", "ultimate.plan"];

        $featValueMap = [];
        foreach ($planKeys as $tier => $planKey) {
            foreach ($ladder as $featureKey => $limits) {
                $featValueMap[$planKey][] = ["key" => $featureKey, "limit" => $limits[$tier], "type" => "integer"];
            }
        }

        $plans = Plan::all();

        $featuresByCountry = Feature::whereIn('country_id', $plans->pluck('country_id')->unique())
            ->get()
            ->groupBy('country_id')
            ->map(fn($group) => $group->keyBy('key'));

        foreach ($plans as $plan) {
            $featValueArr = collect($featValueMap[$plan->key] ?? []);

            if ($featValueArr->isEmpty()) {
                continue;
            }

            $features = $featuresByCountry->get($plan->country_id, collect());

            foreach ($features as $feature) {
                $feat = $featValueArr->firstWhere('key', $feature->key);

                if (!$feat) {
                    continue;
                }

                PlanFeature::updateOrCreate(
                    [
                        'plan_id' => $plan->id,
                        'feature_id' => $feature->id,
                    ],
                    [
                        'country_id' => $feature->country_id,
                        'value' => $feat['limit'],
                        'type' => $feat['type'],
                        'default' => $feature->default,
                    ]
                );
            }
        }
    }
}
