<?php

namespace Database\Seeders;

use App\Models\Parents;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
class ParentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        for($i = 0; $i <= 20; $i++){
            Parents::create([
                'school_branch_id' => 'b927167b-6cc8-4b31-ad4f-431343b474ce',
                'name' => $faker->name,
                'address' => $faker->address,
                'email' => $faker->email,
                "phone_one" => $faker->phoneNumber(),
                "phone_two" => $faker->phoneNumber(),
                "relationship_to_student" => "mother",
                "preferred_language" => "english"
            ]);
        }
    }
}
