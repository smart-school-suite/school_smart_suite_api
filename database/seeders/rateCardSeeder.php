<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class rateCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = Faker::create();

        // Generate 10 dummy rate cards
        for ($i = 0; $i < 20; $i++) {
            DB::table('rate_cards')->insert([
                'id' => $faker->uuid,  // Generate a unique ID
                'min_students' => $faker->numberBetween(1, 50000),
                'max_students' => $faker->numberBetween(101, 100000),
                'max_school_admins' => $faker->numberBetween(1, 5000),
                'max_teachers' => $faker->numberBetween(1, 5000),
                'monthly_rate_per_student' => $faker->randomFloat(2, 10, 100),
                'yearly_rate_per_student' => $faker->randomFloat(2, 100, 1000),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
