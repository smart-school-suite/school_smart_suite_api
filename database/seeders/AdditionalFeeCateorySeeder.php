<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\AdditionalFeesCategory;
use Illuminate\Support\Str;
use App\Models\AdditionalFees;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AdditionalFeeCateorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Create 10 additional fee categories
        for ($i = 0; $i < 10; $i++) {
            AdditionalFeesCategory::create([
                'id' => Str::uuid(),
                'title' => $faker->sentence(3),
                'school_branch_id' => 'd34a2c1c-8b64-46a4-b8ec-65ba77d9d620'
            ]);
        }

        // Retrieve all fee category IDs
        $feeCategoryIds = AdditionalFeesCategory::pluck('id')->toArray();

        // Update additional fees with random category IDs
        AdditionalFees::all()->each(function ($additionalFee) use ($feeCategoryIds) {
            $additionalFee->additionalfee_category_id = Arr::random($feeCategoryIds);
            $additionalFee->save(); // Save the changes back to the database
        });

    }
}
