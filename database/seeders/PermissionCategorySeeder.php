<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PermissionCategory;
class PermissionCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

       $data = [
            [
                "title" => "Student Manager",
                "description" => "Handles student enrollment, records, and profile management."
            ],
            [
                "title" => "Level Manager",
                "description" => "Handles student enrollment, records, and profile management."
            ],
            [
                "title" => "School Admin Manager",
                "description" => "Handles student enrollment, records, and profile management."
            ],
            [
                "title" => "Subscription Manager",
                "description" => "Handles student enrollment, records, and profile management."
            ],
            [
                "title" => "Role Manager",
                "description" => "Handles student enrollment, records, and profile management."
            ],
            [
                "title" => "Permission Manager",
                "description" => "Handles student enrollment, records, and profile management."
            ],
            [
                "title" => "Rate Card Manager",
                "description" => "Handles student enrollment, records, and profile management."
            ],
            [
                "title" => "Event Manager",
                "description" => "Handles student enrollment, records, and profile management."
            ],
            [
                "title" => "Registration Fee Manager",
                "description" => "Handles student enrollment, records, and profile management."
            ],
            [
                "title" => "Parent Manager",
                "description" => "Manages parent profiles, contact information, and related communications."
            ],
            [
                "title" => "Additional Fee Manager",
                "description" => "Manages extra fees beyond standard tuition, such as lab or activity fees."
            ],
            [
                "title" => "Additional Fee Category Manager",
                "description" => "Organizes and categorizes various types of additional fees."
            ],
            [
                "title" => "Country Manager",
                "description" => "Handles country-specific settings, regions, and locale configurations."
            ],
            [
                "title" => "Course Manager",
                "description" => "Manages course creation, updates, and curriculum details."
            ],
            [
                "title" => "Election Manager",
                "description" => "Oversees election processes, candidate registration, and voting procedures."
            ],
            [
                "title" => "Election Application Manager",
                "description" => "Handles applications related to elections, candidate submissions, and approvals."
            ],
            [
                "title" => "App Admin Manager",
                "description" => "Provides administrative access to manage app settings and configurations."
            ],
            [
                "title" => "School Events Manager",
                "description" => "Coordinates school events, schedules, and related notifications."
            ],
            [
                "title" => "Exam Manager",
                "description" => "Organizes exams, schedules, and manages exam results."
            ],
            [
                "title" => "Tuition Fee Manager",
                "description" => "Manages tuition fee structures, payments, and billing."
            ],
            [
                "title" => "Grades Manager",
                "description" => "Handles student grades, grade entries, and reporting."
            ],
            [
                "title" => "Grades Category Manager",
                "description" => "Organizes grades into categories such as exams, assignments, or projects."
            ],
             [
                "title" => "Announcement Category Manager",
                "description" => "Organizes and categorizes various types of announcement"
            ],
             [
                "title" => "Announcement Manager",
                "description" => "Manages All School Announcements"
            ],
            [
                "title" => "Letter Grade Manager",
                "description" => "Sets and manages letter grade scales and grading criteria."
            ],
            [
                "title" => "Marks Manager",
                "description" => "Records and manages student marks and assessments."
            ],
            [
                "title" => "Resit Manager",
                "description" => "Handles resit exams and related scheduling."
            ],
            [
                "title" => "School Manager",
                "description" => "Oversees overall school information, settings, and operations."
            ],
            [
                "title" => "School Branch Manager",
                "description" => "Manages different branches or campuses of the school."
            ],
            [
                "title" => "School Semester Manager",
                "description" => "Handles semester schedules, durations, and academic periods."
            ],
            [
                "title" => "Semester Manager",
                "description" => "Organizes and manages semester-specific data and activities."
            ],
            [
                "title" => "School Expenses Manager",
                "description" => "Tracks and manages school-related expenses and budgets."
            ],
            [
                "title" => "Specialty Manager",
                "description" => "Handles specialized programs, courses, or departments within the school."
            ],
            [
                "title" => "Student Results Manager",
                "description" => "Handles processing, storage, and retrieval of student results."
            ],
            [
                "title" => "Department Manager",
                "description" => "Handles processing, storage, and retrieval of student results."
            ],
            [
                "title" => "Subscription Payment Manager",
                "description" => "Manages subscription plans, payments, and billing cycles."
            ],
            [
                "title" => "Event Category Manager",
                "description" => "Manages Event Categories",
            ],
            [
               "title" => "Teacher Course Preference Manager",
               "description" => "Manages Teacher Course Preference"
            ],
            [
                "title" => "Teacher Manager",
                "description" => "Handles teacher profiles, assignments, and schedules."
            ],
             [
                "title" => "School Branch Setting Manager",
                "description" => "Manages All Settings Related To The School"
            ],
            [
                "title" => "Hall Manager",
                "description" => "Manages All The Related Features in the school"
            ]
        ];
        foreach ($data as $entry) {
            PermissionCategory::create([
                'title' => $entry['title'],
                'description' => $entry['description']
            ]);
        }
    }
}
