<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Student;
use App\Models\Specialty;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TuitionFeesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Student::chunk(200, function ($students) { // Use chunk for large datasets
            foreach ($students as $student) {
                $specialty = Specialty::find($student->specialty_id);

                if ($specialty) {
                    DB::table('tuition_fees')->insert([
                        'id' => Str::uuid(),
                        'student_id' => $student->id,
                        'school_branch_id' => $student->school_branch_id,
                        'specialty_id' => $student->specialty_id,
                        'level_id' => $student->level_id,
                        'amount_paid' => 0.00,
                        'amount_left' => 0.00,
                        'tution_fee_total' => $specialty->school_fee,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }
}
