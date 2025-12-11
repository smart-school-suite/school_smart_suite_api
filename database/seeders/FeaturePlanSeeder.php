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
        $plans = Plan::all();
        $featValueMap = [
            "starter.plan" => [
                [
                    "key" => "timetable.generation",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.admins",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "rate.limit",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.announcements",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.events",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.elections",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.departments",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.specialties",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.courses",
                    "limit" => 10,
                    "type" => "integer"
                ]
            ],
            "growth.plan" => [
                [
                    "key" => "timetable.generation",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.admins",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "rate.limit",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.announcements",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.events",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.elections",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.departments",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.specialties",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.courses",
                    "limit" => 10,
                    "type" => "integer"
                ]
            ],
            "professional.plan" => [
                [
                    "key" => "timetable.generation",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.admins",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "rate.limit",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.announcements",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.events",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.elections",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.departments",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.specialties",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.courses",
                    "limit" => 10,
                    "type" => "integer"
                ]
            ],
            "enterpise.plan" => [
                [
                    "key" => "timetable.generation",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.admins",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "rate.limit",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.announcements",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.events",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.elections",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.departments",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.specialties",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.courses",
                    "limit" => 10,
                    "type" => "integer"
                ]
            ],
            "ultimate.plan" => [
                [
                    "key" => "timetable.generation",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.admins",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "rate.limit",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.announcements",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.events",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.elections",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.departments",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.specialties",
                    "limit" => 20,
                    "type" => "integer"
                ],
                [
                    "key" => "school.courses",
                    "limit" => 10,
                    "type" => "integer"
                ]
            ]
        ];
        foreach ($plans as $plan) {
            $features = Feature::where("country_id", $plan->country_id)->get();
            foreach ($features as $feature) {
                $featValueArr = collect($featValueMap[$plan->key]);
                $feat = $featValueArr->firstWhere('key', $feature->key);
                PlanFeature::create([
                    'plan_id' => $plan->id,
                    'feature_id' => $feature->id,
                    'country_id' => $feature->country_id,
                    "value" => $feat['limit'],
                    "type" => $feat['type'],
                    "default" => $feature->default
                ]);
            }
        }
    }
}
