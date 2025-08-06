<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StatTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kpiDetails = [
            [
                'name' => "Student Ca Percentage Increase In Performance By Exam",
                'program_name' => 'student_ca_percentage_increase_performance_by_exam_type'
            ],
            [
                'name' => "Student Ca Percentage Increase In Performance By Semester",
                'program_name' => "student_ca_percentage_increase_performance_by_semester",
            ],
            [
                'name' => "Student Ca Percentage Decrease In Performance By Exam",
                'program_name' => 'student_ca_percentage_decrease_performance_by_exam'
            ],
            [
                'name' => 'Student Ca Percentage Decrease In Performance By Semester',
                'program_name' => 'student_ca_percentage_decrease_performance_by_semester'
            ],
            [
                'name' => 'Student CA Courses Sat',
                'program_name' => 'student_ca_courses_sat'
            ],
            [
                'name' => 'Student CA Courses Passed',
                'program_name' => 'student_ca_courses_passed'
            ],
            [
                'name' => 'Student CA Courses Failed',
                'program_name' => 'student_ca_courses_failed'
            ],
            [
                'name' => 'Student CA Pass Rate',
                'program_name' => 'student_ca_pass_rate'
            ],
            [
                'name' => 'Student CA Fail Rate',
                'program_name' => 'student_ca_fail_rate'
            ],
            [
                'name' => 'Student CA GPA trends over school years By Exam',
                'program_name' => 'student_ca_school_year_on_gpa_changes_by_exam'
            ],
            [
                'name' => 'Student CA Total Score Trends Over School Years By Exam',
                'program_name' => 'student_ca_school_year_on_total_score_changes_by_exam'
            ],
            [
                'name' => 'Student CA Potential Resits',
                'program_name' => 'student_ca_potential_resits'
            ],
            [
                'name' => 'Student CA Resit Probability',
                'program_name' => 'student_ca_chances_of_resit'
            ],
            [
                'name' => 'Student CA Grades Distribution',
                'program_name' => 'student_ca_grades_distribution'
            ],
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
            [
                'name' => 'Total Students Accessed',
                'program_name' => 'exam_total_students_accessed'
            ],
            [
                'name' => 'Total Students Passed',
                'program_name' => 'exam_total_students_passed'
            ],
            [
                'name' => 'Total Students Failed',
                'program_name' => 'exam_total_students_failed'
            ],
            [
                'name' => 'Pass Rate',
                'program_name' => 'exam_pass_rate'
            ],
            [
                'name' => 'Fail Rate',
                'program_name' => 'exam_fail_rate'
            ],
            [
                'name' => 'Average Total Score',
                'program_name' => 'average_exam_total_score'
            ],
            [
                'name' => 'Average GPA',
                'program_name' => 'average_exam_gpa'
            ],
            [
                'name' => 'Course Fail Rates',
                'program_name' => 'exam_course_fail_rates'
            ],
            [
                'name' => 'Course Pass Rates',
                'program_name' => 'exam_course_pass_rates'
            ],
            [
                'name' => 'Course Fail Distribution',
                'program_name' => 'exam_course_fail_distribution'
            ],
            [
                'name' => 'Course Pass Distribution',
                'program_name' => 'exam_course_pass_distribution'
            ],
            [
                'name' => 'Course Resit Distribution',
                'program_name' => 'exam_course_resit_distribution'
            ],
            [
                'name' => 'Total Number of Resits',
                'program_name' => 'exam_total_number_of_resits'
            ],
            [
                'name' => 'Grades Distribution',
                'program_name' => 'exam_grades_distribution'
            ],
            [
                'name' => 'Course Score Distribution',
                'program_name' => 'exam_course_score_distribution'
            ],
            ['name' => 'Total Additional Fee', 'program_name' => 'total_additional_fee'],
            ['name' => 'Total Additional Fee by Department', 'program_name' => 'total_additional_fee_by_department'],
            ['name' => 'Total Additional Fee by Specialty', 'program_name' => 'total_additional_fee_by_specialty'],
            ['name' => 'Additional Fee Total Amount Paid', 'program_name' => 'additional_fee_total_amount_paid'],
            ['name' => 'Additional Fee Total Amount Paid by Department', 'program_name' => 'additional_fee_total_amount_paid_by_department'],
            ['name' => 'Additional Fee Total Amount Paid by Specialty', 'program_name' => 'additional_fee_total_amount_paid_by_specialty'],
            ['name' => 'Registration Fee Total Amount Paid', 'program_name' => 'registration_fee_total_amount_paid'],
            ['name' => 'Registration Fee Total Amount Paid by Department', 'program_name' => 'registration_fee_total_amount_paid_by_department'],
            ['name' => 'Registration Fee Total Amount Paid by Specialty', 'program_name' => 'registration_fee_total_amount_paid_by_specialty'],
            ['name' => 'Resit Fee Total Amount Paid', 'program_name' => 'resit_fee_total_amount_paid'],
            ['name' => 'Resit Fee Total Amount Paid by Department', 'program_name' => 'resit_fee_total_amount_paid_by_department'],
            ['name' => 'Resit Fee Total Amount Paid by Specialty', 'program_name' => 'resit_fee_total_amount_paid_by_specialty'],
            ['name' => 'Monthly School Expenses', 'program_name' => 'monthly_school_expenses'],
            ['name' => 'Total Tuition Fee Debt', 'program_name' => 'total_tuition_fee_debt'],
            ['name' => 'Total Tuition Fee Debt by Department', 'program_name' => 'total_tuition_fee_debt_by_department'],
            ['name' => 'Total Tuition Fee Debt by Specialty', 'program_name' => 'total_tuition_fee_debt_by_specialty'],
            ['name' => 'Total Tuition Fee Amount Paid', 'program_name' => 'total_tuition_fee_amount_paid'],
            ['name' => 'Total Tuition Fee Paid by Department', 'program_name' => 'total_tuition_fee_paid_by_department'],
            ['name' => 'Total Tuition Fee Paid by Specialty', 'program_name' => 'total_tuition_fee_paid_by_specialty'],
            ['name' => 'Total Indebted Students', 'program_name' => 'total_indepted_students'],
            ['name' => 'Indebted Students by Department', 'program_name' => 'total_indepted_student_by_department'],
            ['name' => 'Indebted Students by Specialty', 'program_name' => 'total_indepted_student_by_specialty'],
            ['name' => 'Total Announcement Count', 'program_name' => 'total_announcement_count'],
            ['name' => 'Announcement Count by Type', 'program_name' => 'total_announcement_count_by_type'],
            ['name' => 'Total Courses Count', 'program_name' => 'total_courses_count'],
            ['name' => 'Courses Count by Specialty', 'program_name' => 'total_courses_count_by_specialty'],
            ['name' => 'Courses Count by Department', 'program_name' => 'total_courses_count_by_department'],
            ['name' => 'Total Number of Departments', 'program_name' => 'total_number_of_departments'],
            ['name' => 'Total Election Count', 'program_name' => 'total_election_count'],
            ['name' => 'Election Type Count by Election', 'program_name' => 'total_election_type_count_by_election'],
            ['name' => 'Total Votes by Election', 'program_name' => 'total_election_votes_by_election'],
            ['name' => 'Election Votes by Department', 'program_name' => 'total_election_votes_by_department'],
            ['name' => 'Election Votes by Specialty', 'program_name' => 'total_election_votes_by_specialty'],
            ['name' => 'Election Role Winner Total Vote', 'program_name' => 'election_role_winner_total_vote'],
            ['name' => 'Election Role Winner by Department', 'program_name' => 'election_role_winner_by_department'],
            ['name' => 'Election Role Winner by Specialty', 'program_name' => 'election_role_winner_by_specialty'],
            ['name' => 'Election Role Winner by Male Gender', 'program_name' => 'election_role_winner_by_male_gender'],
            ['name' => 'Election Role Winner by Female Gender', 'program_name' => 'election_role_Winner_by_female_gender'],
            ['name' => 'Total Number of Specialties', 'program_name' => 'total_number_of_specialties'],
            ['name' => 'Registered Students Count Over Time', 'program_name' => 'registered_students_count_over_time'],
            ['name' => 'Female Registered Students Over Time', 'program_name' => 'female_registered_student_count_over_time'],
            ['name' => 'Male Registered Students Over Time', 'program_name' => 'male_registered_student_count_over_time'],
            ['name' => 'Specialty Registration Count Over Time', 'program_name' => 'specialty_registration_count_over_time'],
            ['name' => 'Department Registration Count Over Time', 'program_name' => 'department_registration_count_over_time'],
            ['name' => 'Registered Teachers Count Over Time', 'program_name' => 'registered_teachers_count_over_time'],
            ['name' => 'Female Registered Teachers Over Time', 'program_name' => 'female_registered_teachers_count_over_time'],
            ['name' => 'Male Registered Teachers Over Time', 'program_name' => 'male_registered_teachers_count_over_time'],
            ['name' => 'Total Number of Courses per Semester', 'program_name' => 'total_number_of_courses_per_semester'],
            ['name' => 'Total Number of Courses per Specialty', 'program_name' => 'total_number_of_courses_per_specialty'],
            ['name' => 'Total Number of Courses per Teacher', 'program_name' => 'total_number_of_courses_per_teacher'],
            ['name' => 'Average Courses per Day', 'program_name' => 'total_average_course_per_day'],
            [
                'name' => "School Expenses Progress",
                "program_name" => "school_expenses_progress"
            ],
            [
                'name' => "Total Tuition Fee Progress",
                "program_name" => "total_tuition_fee_progress",
            ],
            [
                'name' => "Total Additional Fee Progress",
                "program_name" => "total_additional_fee_progress"
            ],
            [
                'name' => 'Total Application Count',
                'program_name' => 'total_application_count'
             ],
             [
                'name' => 'Total Application Rejection Count',
                'program_name' => 'total_application_rejection_count'
             ],
             [
                'name' => 'Total Application Acceptance Count',
                'program_name' => 'total_application_acceptance_count'
             ],
             [
                'name' => 'School Expenses Progress',
                'program_name' => 'school_expenses_progress'
             ],
             [
                'name' => 'Total Revenue Progress',
                'program_name' => 'total_revenue_progress'
             ],
             [
                'name' => "Total School Admin",
                'program_name' => 'total_school_admin'
             ]
        ];
        $this->command->info("Creating Stat Types..............................................1%");
        foreach ($kpiDetails as $kpiDetail) {
            DB::table('stat_types')->insert(
                [
                    'id' => Str::uuid(),
                    'name' => $kpiDetail['name'],
                    'program_name' => $kpiDetail['program_name'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }
        $this->command->info("Stat Types Created Successfully..................................100%");
    }
}
