<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Student;
use App\Models\Exams;
use App\Models\AccessedStudent;
use Illuminate\Database\Seeder;

class AccessedStudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Fetch all students
        $students = Student::all();

        // Loop through each student
        foreach ($students as $student) {

            $exams = Exams::where('level_id', $student->level_id)
                ->where('specialty_id', $student->specialty_id)
                ->where('school_branch_id', $student->school_branch_id)
                ->get();

            // Loop through the exams and create AccessedStudent records
            foreach ($exams as $exam) {
                AccessedStudent::create([
                    'student_id' => $student->id,
                    'exam_id' => $exam->id,
                    'school_branch_id' => $student->school_branch_id,
                    'grades_submitted' => false, // assuming default false
                    'student_accessed' => 'pending'  // assuming default false
                ]);
            }
        }
    }
}
