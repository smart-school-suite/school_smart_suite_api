<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Faker as Faker;
use Illuminate\Database\Seeder;

class StatTypes extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        $generalExamKPIs = [
            [
              "name" => "Pass Rate",
              "program_name" => "pass_rate"
            ],
            [
              "name" => "Average Score",
              "program_name" => "average_score"
            ],
            [
              "name" => "Distribution of Scores",
              "program_name" => "distribution_of_scores"
            ],
            [
              "name" => "Standard Deviation",
              "program_name" => "standard_deviation"
            ],
            [
              "name" => "Exam Completion Rate",
              "program_name" => "exam_completion_rate"
            ],
            [
              "name" => "Retention Rate",
              "program_name" => "retention_rate"
            ],
            [
              "name" => "Time Taken to Complete Exam",
              "program_name" => "time_taken_to_complete_exam"
            ],
            [
              "name" => "Individual Student Progress",
              "program_name" => "individual_student_progress"
            ],
            [
              "name" => "Subject Performance Analysis",
              "program_name" => "subject_performance_analysis"
            ],
            [
              "name" => "Exam Retake Rate",
              "program_name" => "exam_retake_rate"
            ],
            [
              "name" => "Performance by Demographics",
              "program_name" => "performance_by_demographics"
            ],
            [
              "name" => "Total Number of Students Accessed",
              "program_name" => "total_students_accessed"
            ],
            [
              "name" => "Total Number of Students Passed",
              "program_name" => "total_students_passed"
            ],
            [
              "name" => "Student Retention Rates",
              "program_name" => "student_retention_rates"
            ],
            [
              "name" => "Subject Proficiency Rates",
              "program_name" => "subject_proficiency_rates"
            ],
            [
              "name" => "Courses with the Highest Fail Rate",
              "program_name" => "highest_fail_rate_courses"
            ],
            [
              "name" => "Courses with the Highest Pass Rate",
              "program_name" => "highest_pass_rate_courses"
            ],
            [
              "name" => "Top 10% of Students",
              "program_name" => "top_10_percent_students"
            ],
            [
              "name" => "Percentage of Students with Honors or Distinction",
              "program_name" => "percentage_students_honors_distinction"
            ],
            [
              "name" => "Average GPA",
              "program_name" => "average_gpa"
            ]
        ];

        $studentExamKPIs = [
            [
              "name" => "Courses Sat",
              "program_name" => "courses_sat"
            ],
            [
              "name" => "Courses Passed",
              "program_name" => "courses_passed"
            ],
            [
              "name" => "Courses Failed",
              "program_name" => "courses_failed"
            ],
            [
              "name" => "Pass Rate by Course",
              "program_name" => "pass_rate_by_course"
            ],
            [
              "name" => "Fail Rate by Course",
              "program_name" => "fail_rate_by_course"
            ],
            [
              "name" => "Total Scores by Course",
              "program_name" => "total_scores_by_course"
            ],
            [
              "name" => "Grades Distribution by Course",
              "program_name" => "grades_distribution_by_course"
            ],
            [
              "name" => "Overall Score",
              "program_name" => "overall_score"
            ],
            [
              "name" => "Subject/Topic Proficiency",
              "program_name" => "subject_topic_proficiency"
            ],
            [
              "name" => "Progress Over Time",
              "program_name" => "progress_over_time"
            ],
            [
              "name" => "Class Average vs. Individual Performance",
              "program_name" => "class_avg_vs_individual_performance"
            ],
            [
              "name" => "Student Ranking",
              "program_name" => "student_ranking"
            ],
            [
              "name" => "Percentage Increase in Performance",
              "program_name" => "percentage_increase_performance"
            ],
            [
              "name" => "Percentage Decrease in Performance",
              "program_name" => "percentage_decrease_performance"
            ],
            [
              "name" => "Score Trends Over Time",
              "program_name" => "score_trends_over_time"
            ],
            [
              "name" => "Consistency of Scores",
              "program_name" => "consistency_of_scores"
            ],
            [
              "name" => "Average Class Performance",
              "program_name" => "average_class_performance"
            ],
            [
              "name" => "Best Exam Score",
              "program_name" => "best_exam_score"
            ],
            [
              "name" => "Worst Exam Score",
              "program_name" => "worst_exam_score"
            ]
        ];

          $faker = Faker\Factory::create();
          foreach ($generalExamKPIs as $generalExamKPI) {
              DB::table('stat_types')->insert([
                  'id' => $faker->uuid,
                  'stat_category_id' => 'd09a6094-b56f-3a98-9483-ef99920b090e',
                  'name' => $generalExamKPI['name'],
                  'description' => $faker->sentence,
                  'program_name' => $generalExamKPI['program_name'],
                  'status' => 'active',
                  'created_at' => now(),
                  'updated_at' => now(),
              ]);
          }
          foreach ($studentExamKPIs as $studentExamKPI) {
            DB::table('stat_types')->insert([
                'id' => $faker->uuid,
                'stat_category_id' => '574c391d-990a-3164-a89e-26c503927e74',
                'name' => $studentExamKPI['name'],
                'description' => $faker->sentence,
                'program_name' => $studentExamKPI['program_name'],
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $this->command->info("Database seeded successfully");
    }
}
