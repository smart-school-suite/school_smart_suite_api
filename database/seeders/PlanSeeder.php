<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Plan;
use App\Models\Country;
class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                "name" => "Starter Plan",
                "description" => "Perfect for small schools just getting started. Includes essential tools to manage classes, students, and basic operations.",
                "price" => 100000,
                "key" => "starter.plan",
                "max_plan" => false
            ],
            [
                "name" => "Growth Plan",
                "description" => "Ideal for growing schools that need more features, more admin accounts, and expanded operational capacity.",
                "price" => 250000,
                "key" => "growth.plan",
                "max_plan" => false
            ],
            [
                "name" => "Professional Plan",
                "description" => "Designed for mid-sized institutions requiring advanced management tools, analytics, enhanced limits, and automation.",
                "price" => 500000,
                "key" => "professional.plan",
                "max_plan" => false
            ],
            [
                "name" => "Enterprise Plan",
                "description" => "A complete, high-capacity solution for large schools and institutions with unlimited access to features, priority support, and the highest resource limits.",
                "price" => 750000,
                "key" => "enterpise.plan",
                "max_plan" => false
            ],
            [
                "name" => "Ultimate Plan",
                "description" => "Our top-tier package with full customization, dedicated account support, unlimited scale, and all premium features unlocked.",
                "price" => 1000000,
                "key" => "ultimate.plan",
                "max_plan" => true
            ]
        ];

        $countries = Country::all();
        foreach($countries as $country){
             foreach($plans as $plan){
                 Plan::create([
                     'country_id' => $country->id,
                     'price' => $plan["price"],
                     "name" => $plan['name'],
                     "description" => $plan["description"],
                     "key" => $plan["key"],
                     "max_plan" => $plan["max_plan"]
                 ]);
             }
        }
    }
}
