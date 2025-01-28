<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

class permissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(['id' => Str::uuid(), 'name' => "assign-permissions", "guard" => "api"]);
        Permission::create(["id" => Str::uuid(), 'name' => "revoke-permissions", "guard" => "api"]);
        Permission::create(["id" => Str::uuid(), 'name' => "revoke-all-permissions", "guard" => "api"]);
        Permission::create(["id" => Str::uuid(), "name"=> "view-roles", "guard" => "api"]);
        Permission::create(["id" => Str::uuid(), "name"=> "view-permissions", "guard" => "api"]);
        Permission::create(["id"=> Str::uuid(), "name"=> "view-admin-permissions", "guard" => "api"]);
        Permission::create(["id"=> Str::uuid(), "name"=> "assign-super-admin", "guard" => "api"]);
         // Student permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-student', 'guard' => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-student', 'guard' => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-student', 'guard' => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-student', 'guard' => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'generate-report-card', 'guard' => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'promote-student', 'guard' => "api"]);


         // Admin permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-admin', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-admin', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-admin', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-admin', "guard" => "api"]);

         // Course permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-course', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-course', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-course', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-course', "guard" => "api"]);

         // Parent permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-parent', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-parent', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-parent', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-parent', "guard" => "api"]);

         // Teacher permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-teacher', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-teacher', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-teacher', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-teacher', "guard" => "api"]);

         // Department permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-department', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-department', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-department', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-department', "guard" => "api"]);

         // Education level permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-level', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-level', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-level', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-level', "guard" => "api"]);

         // App administration permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-app-admin', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-app-admin', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-app-admin', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-app-admin', "guard" => "api"]);

         // Event permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-event', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-event', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-event', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-event', "guard" => "api"]);

         // Exam permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-exam', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-exam', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-exam', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-exam', "guard" => "api"]);
         Permission::create(["id" => Str::uuid(),   "name" => "view-letter-grades", "guard" => "api"]);
         Permission::create(['id' => Str::uuid(), 'name' => 'view-accessed-exam', "guard" => "api"]);

         // Exam timetable permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-exam-timetable', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-exam-timetable', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-exam-timetable', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-exam-timetable', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-exam-courses', "guard" => "api"]);

         // Exam type permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-exam-type', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-exam-type', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-exam-type', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-exam-type', "guard" => "api"]);

         // Fee payment permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-fee-payment', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-fee-payment', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-fee-payment', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-fee-payment', "guard" => "api"]);

         // Grade permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-grade', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-grade', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-grade', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-grade', "guard" => "api"]);

         // Report card permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-report-card', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-report-card', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-report-card', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-report-card', "guard" => "api"]);

         // Resit courses permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-resit-course', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-resit-course', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-resit-course', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-resit-course', "guard" => "api"]);

         // Resit exam timetable permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-resit-exam-timetable', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-resit-exam-timetable', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-resit-exam-timetable', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-resit-exam-timetable', "guard" => "api"]);

         // School permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-school', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-school', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-school', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-school', "guard" => "api"]);

         // School branch permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-school-branch', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-school-branch', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-school-branch', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-school-branch', "guard" => "api"]);

         // School expenses permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-school-expense', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-school-expense', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-school-expense', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-school-expense', "guard" => "api"]);

         // Semester permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-semester', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-semester', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-semester', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-semester', "guard" => "api"]);

         // Specialty permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-specialty', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-specialty', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-specialty', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-specialty', "guard" => "api"]);

         // Student batch permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-student-batch', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-student-batch', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-student-batch', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-student-batch', "guard" => "api"]);

         // Specialty time-table permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-specialty-time-table', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-specialty-time-table', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-specialty-time-table', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-specialty-time-table', "guard" => "api"]);

         // Student resit permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-student-resit', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-student-resit', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-student-resit', "guard" => "api"]);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-student-resit', "guard" => "api"]);

         Permission::create(["id" => Str::uuid(), "name"=> "create-department", "guard" => "api"]);
         Permission::create(["id" => Str::uuid(), "name"=> "update-department", "guard" => "api"]);
         Permission::create(["id" => Str::uuid(), "name"=> "delete-department", "guard" => "api"]);
         Permission::create(["id" => Str::uuid(), "name"=> "view-department", "guard" => "api"]);

         Permission::create(["id"=> Str::uuid(), "name"=> "fill-scores", "guard" => "api"]);
         Permission::create(["id"=> Str::uuid(), "name"=> "edit-scores", "guard" => "api"]);
         Permission::create(["id"=> Str::uuid(), "name"=> "delete-scores", "guard" => "api"]);
         Permission::create(["id"=> Str::uuid(), "name"=> "view-scores", "guard" => "api"]);
         Permission::create(["id"=> Str::uuid(), "name"=> "view-accessed-courses", "guard" => "api"]);




         Permission::create(["id"=> Str::uuid(), "name"=> "create-avaliability", "guard" => "api"]);
         Permission::create(["id"=> Str::uuid(), "name"=> "delete-avaliability", "guard" => "api"]);
         Permission::create(["id"=> Str::uuid(), "name"=> "edit-avaliability", "guard" => "api"]);
         Permission::create(["id"=> Str::uuid(), "name"=> "view-avaliability", "guard" => "api"]);




    }
}
