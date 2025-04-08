<?php

namespace Database\Seeders;

use App\Models\AccessedResitStudent;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Exams;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class AccessedResitCandidateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $exams = Exams::where("school_branch_id", "d34a2c1c-8b64-46a4-b8ec-65ba77d9d620")
        ->whereHas('examType', function($query) {
        $query->where('type', 'resit');})->pluck('id')->toArray();
        $students = Student::where("school_branch_id", "d34a2c1c-8b64-46a4-b8ec-65ba77d9d620")
            ->pluck('id')->toArray();
        for( $i = 0; $i < 3000; $i++) {
            AccessedResitStudent::create([
                'school_branch_id' => 'd34a2c1c-8b64-46a4-b8ec-65ba77d9d620',
                'student_id' => Arr::random($students),
                'exam_id' => Arr::random($exams),
                'student_accessed' => 'pending',
            ]);
        }

    }
}
