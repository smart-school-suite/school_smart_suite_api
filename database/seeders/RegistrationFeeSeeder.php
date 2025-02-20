<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Student;
use App\Models\Specialty;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class RegistrationFeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Student::chunk(200, function ($students) { // Use chunk for large datasets
            foreach ($students as $student) {
                $specialty = Specialty::find($student->specialty_id);

                if ($specialty) {
                    DB::table('registration_fees')->insert([
                        'id' => Str::uuid(),
                        'student_id' => $student->id,
                        'school_branch_id' => $student->school_branch_id,
                        'specialty_id' => $student->specialty_id,
                        'level_id' => $student->level_id,
                        'amount' => $specialty->registration_fee,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }
}
