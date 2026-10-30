<?php

namespace Database\Seeders;

use App\Models\Examtype;
use Illuminate\Database\Seeder;

class ExamTypeSeeder extends Seeder
{
    public function run(): void
    {
        $examTypesData = [
            [
                'exam_name'    => 'Second Semester Exam',
                'program_name' => 'SSE',
                'semester'     => 'second',
                'type'         => 'exam',
                'description'  => 'The final major exam of the entire academic session, usually written in May/June or June/July. Covers only second-semester topics, but your result here combines with everything else to determine if you pass or move to the next class/level. Typically 60–70%. Clear this and you’re done for the year – no more tension!',
            ],
            [
                'exam_name'    => 'Second Semester CA (Continous Accessment)',
                'program_name' => 'SSC',
                'semester'     => 'second',
                'type'         => 'ca',
                'description'  => 'Just like first semester CA, but for the second half of the session (January/February to May/June). All your tests, assignments, quizzes, practicals, and attendance during this period count here. Still 30–40% of your final grade. Many students use this semester to boost subjects they were weak in earlier.',
            ],
            [
                'exam_name'    => 'Second Semester Resit',
                'program_name' => 'SSR',
                'semester'     => 'second',
                'type'         => 'resit',
                'description'  => 'A supplementary examination opportunity provided at the end of the academic session for students needing to clear failed or missed courses from the second semester. Passing this allows you to satisfy academic requirements without repeating the entire semester.',
            ],
            [
                'exam_name'    => 'First Semester Resit',
                'program_name' => 'FSR',
                'semester'     => 'first',
                'type'         => 'resit',
                'description'  => 'A makeup assessment offered to students who scored below the passing threshold or missed the main first semester examination. It provides a second chance to earn necessary course credits before progressing further.',
            ],
            [
                'exam_name'    => 'First Semester CA (Continous Accessment)',
                'program_name' => 'FSC',
                'semester'     => 'first',
                'type'         => 'ca',
                'description'  => 'This is your day-to-day score throughout the first semester (usually August/September to December). It includes quizzes, class tests, assignments, projects, attendance, and participation. In most schools, CA is worth 30–40% of your total grade for each subject. Staying consistent here reduces the pressure on the final exam and keeps your GP high from the start!',
            ],
            [
                'exam_name'    => 'First Semester Exam',
                'program_name' => 'FSE',
                'semester'     => 'first',
                'type'         => 'exam',
                'description'  => 'The main written exam taken at the end of first semester (November/December/January). It tests everything you’ve been taught from the beginning of the session till that point. Usually carries 60–70% of your total grade per subject. Doing well here locks in a strong foundation for the year.',
            ],
        ];

        foreach ($examTypesData as $row) {
            $examType = Examtype::firstOrNew(['program_name' => $row['program_name']]);
            $examType->exam_name   = $row['exam_name'];
            $examType->semester    = $row['semester'];
            $examType->type        = $row['type'];
            $examType->description = $row['description'];

            $examType->save();
        }
    }
}
