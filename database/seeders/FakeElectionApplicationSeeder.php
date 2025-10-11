<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ElectionApplication;
use App\Models\ElectionRoles;
use App\Models\Elections;
use App\Models\Student;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
class FakeElectionApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $faker = Faker::create();

        $election = Elections::first();
        if (!$election) {
            return;
        }

        $electionRoles = ElectionRoles::where("election_type_id", $election->election_type_id)->get();
        if ($electionRoles->isEmpty()) {
            return;
        }

        $students = Student::get();
        if ($students->isEmpty()) {
            return;
        }

        $numberOfApplications = rand(100, 500);

        foreach (range(1, $numberOfApplications) as $index) {
            $student = $students->random();
            $role = $electionRoles->random();

            ElectionApplication::create([
                'id' => Str::uuid(),
                'school_branch_id' => $election->school_branch_id ?? 1,
                'election_id' => $election->id,
                'election_role_id' => $role->id,
                'student_id' => $student->id,
                'manifesto' => $faker->paragraph(3),
                'personal_vision' => $faker->sentence(10),
                'commitment_statement' => $faker->sentence(15),
            ]);
        }
    }
}
