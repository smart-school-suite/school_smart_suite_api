<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;
use Illuminate\Support\Str;

class permissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create([ 'name' => "assign-permissions", "guard_name" => "api"]);
        Permission::create([ 'name' => "revoke-permissions", "guard_name" => "api"]);
        Permission::create([ 'name' => "revoke-all-permissions", "guard_name" => "api"]);
        Permission::create([ "name"=> "view-roles", "guard_name" => "api"]);
        Permission::create([ "name"=> "view-permissions", "guard_name" => "api"]);
        Permission::create([ "name"=> "view-admin-permissions", "guard_name" => "api"]);
        Permission::create([ "name"=> "assign-super-admin", "guard_name" => "api"]);
         // Student permissions
         Permission::create([ 'name' => 'create-student', 'guard_name' => "api"]);
         Permission::create([ 'name' => 'edit-student', 'guard_name' => "api"]);
         Permission::create([ 'name' => 'view-student', 'guard_name' => "api"]);
         Permission::create([ 'name' => 'delete-student', 'guard_name' => "api"]);
         Permission::create([ 'name' => 'generate-report-card', 'guard_name' => "api"]);
         Permission::create([ 'name' => 'promote-student', 'guard_name' => "api"]);


         // Admin permissions
         Permission::create([ 'name' => 'create-admin', "guard_name" => "api"]);
         Permission::create([ 'name' => 'edit-admin', "guard_name" => "api"]);
         Permission::create([ 'name' => 'view-admin', "guard_name" => "api"]);
         Permission::create([ 'name' => 'delete-admin', "guard_name" => "api"]);

         // Course permissions
         Permission::create([ 'name' => 'create-course', "guard_name" => "api"]);
         Permission::create([ 'name' => 'edit-course', "guard_name" => "api"]);
         Permission::create([ 'name' => 'view-course', "guard_name" => "api"]);
         Permission::create([ 'name' => 'delete-course', "guard_name" => "api"]);

         // Parent permissions
         Permission::create([ 'name' => 'create-parent', "guard_name" => "api"]);
         Permission::create([ 'name' => 'edit-parent', "guard_name" => "api"]);
         Permission::create([ 'name' => 'view-parent', "guard_name" => "api"]);
         Permission::create([ 'name' => 'delete-parent', "guard_name" => "api"]);

         // Teacher permissions
         Permission::create([ 'name' => 'create-teacher', "guard_name" => "api"]);
         Permission::create([ 'name' => 'edit-teacher', "guard_name" => "api"]);
         Permission::create([ 'name' => 'view-teacher', "guard_name" => "api"]);
         Permission::create([ 'name' => 'delete-teacher', "guard_name" => "api"]);

         // Department permissions
         Permission::create([ 'name' => 'create-department', "guard_name" => "api"]);
         Permission::create([ 'name' => 'edit-department', "guard_name" => "api"]);
         Permission::create([ 'name' => 'view-department', "guard_name" => "api"]);
         Permission::create([ 'name' => 'delete-department', "guard_name" => "api"]);

         // Education level permissions
         Permission::create([ 'name' => 'create-level', "guard_name" => "api"]);
         Permission::create([ 'name' => 'edit-level', "guard_name" => "api"]);
         Permission::create([ 'name' => 'view-level', "guard_name" => "api"]);
         Permission::create([ 'name' => 'delete-level', "guard_name" => "api"]);

         // App administration permissions
         Permission::create([ 'name' => 'create-app-admin', "guard_name" => "api"]);
         Permission::create([ 'name' => 'edit-app-admin', "guard_name" => "api"]);
         Permission::create([ 'name' => 'view-app-admin', "guard_name" => "api"]);
         Permission::create([ 'name' => 'delete-app-admin', "guard_name" => "api"]);

         // Event permissions
         Permission::create([ 'name' => 'create-event', "guard_name" => "api"]);
         Permission::create([ 'name' => 'edit-event', "guard_name" => "api"]);
         Permission::create([ 'name' => 'view-event', "guard_name" => "api"]);
         Permission::create([ 'name' => 'delete-event', "guard_name" => "api"]);

         // Exam permissions
         Permission::create([ 'name' => 'create-exam', "guard_name" => "api"]);
         Permission::create([ 'name' => 'edit-exam', "guard_name" => "api"]);
         Permission::create([ 'name' => 'view-exam', "guard_name" => "api"]);
         Permission::create([ 'name' => 'delete-exam', "guard_name" => "api"]);
         Permission::create([   "name" => "view-letter-grades", "guard_name" => "api"]);
         Permission::create([ 'name' => 'view-accessed-exam', "guard_name" => "api"]);

         // Exam timetable permissions
         Permission::create([ 'name' => 'create-exam-timetable', "guard_name" => "api"]);
         Permission::create([ 'name' => 'edit-exam-timetable', "guard_name" => "api"]);
         Permission::create([ 'name' => 'view-exam-timetable', "guard_name" => "api"]);
         Permission::create([ 'name' => 'delete-exam-timetable', "guard_name" => "api"]);
         Permission::create([ 'name' => 'view-exam-courses', "guard_name" => "api"]);

         // Exam type permissions
         Permission::create([ 'name' => 'create-exam-type', "guard_name" => "api"]);
         Permission::create([ 'name' => 'edit-exam-type', "guard_name" => "api"]);
         Permission::create([ 'name' => 'view-exam-type', "guard_name" => "api"]);
         Permission::create([ 'name' => 'delete-exam-type', "guard_name" => "api"]);

         // Fee payment permissions
         Permission::create([ 'name' => 'create-fee-payment', "guard_name" => "api"]);
         Permission::create([ 'name' => 'edit-fee-payment', "guard_name" => "api"]);
         Permission::create([ 'name' => 'view-fee-payment', "guard_name" => "api"]);
         Permission::create([ 'name' => 'delete-fee-payment', "guard_name" => "api"]);

         // Grade permissions
         Permission::create([ 'name' => 'create-grade', "guard_name" => "api"]);
         Permission::create([ 'name' => 'edit-grade', "guard_name" => "api"]);
         Permission::create([ 'name' => 'view-grade', "guard_name" => "api"]);
         Permission::create([ 'name' => 'delete-grade', "guard_name" => "api"]);

         // Report card permissions
         Permission::create([ 'name' => 'create-report-card', "guard_name" => "api"]);
         Permission::create([ 'name' => 'edit-report-card', "guard_name" => "api"]);
         Permission::create([ 'name' => 'view-report-card', "guard_name" => "api"]);
         Permission::create([ 'name' => 'delete-report-card', "guard_name" => "api"]);

         // Resit courses permissions
         Permission::create([ 'name' => 'create-resit-course', "guard_name" => "api"]);
         Permission::create([ 'name' => 'edit-resit-course', "guard_name" => "api"]);
         Permission::create([ 'name' => 'view-resit-course', "guard_name" => "api"]);
         Permission::create([ 'name' => 'delete-resit-course', "guard_name" => "api"]);

         // Resit exam timetable permissions
         Permission::create([ 'name' => 'create-resit-exam-timetable', "guard_name" => "api"]);
         Permission::create([ 'name' => 'edit-resit-exam-timetable', "guard_name" => "api"]);
         Permission::create([ 'name' => 'view-resit-exam-timetable', "guard_name" => "api"]);
         Permission::create([ 'name' => 'delete-resit-exam-timetable', "guard_name" => "api"]);

         // School permissions





         Permission::create([ "name"=> "create-avaliability", "guard_name" => "api"]);
         Permission::create([ "name"=> "delete-avaliability", "guard_name" => "api"]);
         Permission::create([ "name"=> "edit-avaliability", "guard_name" => "api"]);
         Permission::create([ "name"=> "view-avaliability", "guard_name" => "api"]);




    }
}
