<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\AdditionalFees;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class AdditionalFeesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $students = Student::all();
        foreach ($students as $student) {
            AdditionalFees::create([
                'id' => Str::uuid(),
                'title' => $faker->sentence(3),
                'reason' => $faker->text(100),
                'amount' => $faker->randomFloat(2, 500, 25000),
                'status' => $faker->randomElement(['paid', 'unpaid', 'pending']),
                'school_branch_id' => "d34a2c1c-8b64-46a4-b8ec-65ba77d9d620",
                'specialty_id' => $student->specialty_id,
                'level_id' => $student->level_id,
                'student_id' => $student->id
            ]);
        }
    }
}
