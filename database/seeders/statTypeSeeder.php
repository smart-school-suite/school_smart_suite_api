<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class statTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kpiNames = [
            [
                'name' => "Percentage Increase In Performance By Exam",
                'program_name' => 'percentage_increase_performance_by_exam_type'
            ],
            [
                'name' => "Percentage Increase In Performance By Semester",
                'program_name' => "percentage_increase_performance_by_semester",
            ],
            [
                'name' => "Percentage Decrease In Performance By Exam",
                'program_name' => 'percentage_decrease_performance_by_exam'
            ],
            [
                'name' => 'Percentage Decrease In Performance By Semester',
                'program_name' => 'percentage_decrease_performance_by_semester'
            ],
            [
                'name' => 'Courses Sat',
                'program_name' => 'courses_sat'
            ],
            [
                'name' => 'Courses Passed',
                'program_name' => 'courses_passed'
            ],
            [
                'name' => 'Pass Rate',
                'program_name' => 'courses_failed'
            ],
            [
                'name' => 'Fail Rate',
                'program_name' => 'fail_rate'
            ],
            [
                'name' => 'GPA trends over school years By Exam',
                'program_name' => 'school_year_on_gpa_changes_by_exam'
            ],
            [
                'name' => 'Total Score Trends Over School Years By Exam',
                'program_name' => 'school_year_on_total_score_changes_by_exam'
            ],
            [
                'name' => 'Potential Resits',
                'program_name' => 'potential_resits'
            ],
            [
                'name' => 'Resit Probability',
                'program_name' => 'chances_of_resit'
            ],
            [
                'name' => 'Grades Distribution',
                'program_name' => 'grades_distribution'
            ]
        ];

        $kpiNamesTwo = [
            [
                'name' => 'CA Exam Total Students Accessed',
                'program_name' => 'ca_exam_total_students_accessed'
            ],
            [
                'name' => 'CA Exam Total Students Passed',
                'program_name' => 'ca_exam_total_students_passed'
            ],
            [
                'name' => 'CA Exam Total Students Failed',
                'program_name' => 'ca_exam_total_students_failed'
            ],
            [
                'name' => 'CA Exam Pass Rate',
                'program_name' => 'ca_exam_pass_rate'
            ],
            [
                'name' => 'CA Exam Fail Rate',
                'program_name' => 'ca_exam_fail_rate'
            ],
            [
                'name' => 'CA Exam Average Total Score',
                'program_name' => 'average_ca_exam_total_score'
            ],
            [
                'name' => 'CA Exam Average GPA',
                'program_name' => 'average_ca_exam_gpa'
            ],
            [
                'name' => 'CA Exam Course Fail Rates',
                'program_name' => 'ca_exam_course_fail_rates'
            ],
            [
                'name' => 'CA Exam Course Pass Rates',
                'program_name' => 'ca_exam_course_pass_rates'
            ],
            [
                'name' => 'CA Exam Course Fail Distribution',
                'program_name' => 'ca_exam_course_fail_distribution'
            ],
            [
                'name' => 'CA Exam Course Pass Distribution',
                'program_name' => 'ca_exam_course_pass_distribution'
            ],
            [
                'name' => 'CA Exam Potential Resit Distribution',
                'program_name' => 'ca_exam_course_potential_resit_distribution'
            ],
            [
                'name' => 'CA Exam Total Potential Resits',
                'program_name' => 'ca_total_number_of_potential_resits'
            ],
            [
                'name' => 'CA Exam Grades Distribution',
                'program_name' => 'ca_exam_grades_distribution'
            ],
            [
                'name' => 'CA Exam Course Score Distribution',
                'program_name' => 'ca_exam_course_score_distribution'
            ],

        ];

$kpiNamesThree = [
    [
        'name' => 'Student Percentage Increase Performance by Exam Type',
        'program_name' => 'student_exam_percentage_increase_performance_by_exam_type'
    ],
    [
        'name' => 'Student Percentage Increase Performance by Semester',
        'program_name' => 'student_exam_percentage_increase_performance_by_semester'
    ],
    [
        'name' => 'Student Percentage Decrease Performance by Exam Type',
        'program_name' => 'student_exam_percentage_decrease_performance_by_exam_type'
    ],
    [
        'name' => 'Student Percentage Decrease Performance by Semester',
        'program_name' => 'student_exam_percentage_decrease_performance_by_semester'
    ],
    [
        'name' => 'Student Courses SAT',
        'program_name' => 'student_exam_courses_sat'
    ],
    [
        'name' => 'Student Courses Passed',
        'program_name' => 'student_exam_courses_passed'
    ],
    [
        'name' => 'Student Courses Failed',
        'program_name' => 'student_exam_courses_failed'
    ],
    [
        'name' => 'Student Exam Pass Rate',
        'program_name' => 'student_exam_pass_rate'
    ],
    [
        'name' => 'Student Exam Fail Rate',
        'program_name' => 'student_exam_fail_rate'
    ],
    [
        'name' => 'Student GPA Changes by Exam and School Year',
        'program_name' => 'student_exam_school_year_on_gpa_changes_by_exam'
    ],
    [
        'name' => 'Student Total Score Changes by Exam and School Year',
        'program_name' => 'student_exam_school_year_on_total_score_changes_by_exam'
    ],
    [
        'name' => 'Student Resits',
        'program_name' => 'student_exam_resits'
    ],
    [
        'name' => 'Student No Resit',
        'program_name' => 'student_exam_no_resit'
    ],
    [
        'name' => 'Student Grades Distribution',
        'program_name' => 'student_exam_grades_distribution'
    ],
    [
        'name' => 'Student Marks Score Distribution by Course',
        'program_name' => 'student_exam_marks_score_distribution_by_course'
    ],
];
        foreach ($kpiNamesThree as $kpiName) {
            DB::table('stat_types')->insert(
                [
                    'id' => Str::uuid(),
                    'name' => $kpiName['name'],
                    'program_name' => $kpiName['program_name']
                ]
            );
        }
        $this->command->info("Stat Type Seeder Inserted Successfully");
    }
}
