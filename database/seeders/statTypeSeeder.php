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
        foreach($kpiNames as $kpiName){
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
