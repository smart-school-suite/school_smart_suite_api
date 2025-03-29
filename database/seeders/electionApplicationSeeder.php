<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Elections;
use App\Models\Student;
use Faker\Factory as Faker;
use App\Models\ElectionRoles;
use Illuminate\Database\Seeder;

class electionApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 500; $i++) {
            $studentId = Student::inRandomOrder()->first()->id;

            $electionRoleId = ElectionRoles::inRandomOrder()->first()->id;
            Elections::create([
                'isApproved' => $faker->boolean,
                'school_branch_id' => 1,
                'election_id' => 1,
                'election_role_id' => $electionRoleId,
                'student_id' => $studentId,
                'manifesto' => $faker->text(200),
                'personal_vision' => $faker->text(150),
                'commitment_statement' => $faker->text(100),
            ]);
        }
    }
}
