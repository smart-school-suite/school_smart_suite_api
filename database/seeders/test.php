<?php

namespace Database\Seeders;

use App\Jobs\ExamJointCourse\CreateExamJointCourseJob;
use App\Models\Exam\Exam;
use Illuminate\Database\Seeder;

class test extends Seeder
{
    public function run(): void
    {
        $exams = Exam::all();
        foreach ($exams as $exam) {
            CreateExamJointCourseJob::dispatch(
                $exam->id,
                $exam->school_branch_id
            );
        }
    }
}
