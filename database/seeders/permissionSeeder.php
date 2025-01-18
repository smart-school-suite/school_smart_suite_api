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
         // Student permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-student']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-student']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-student']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-student']);
 
         // Admin permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-admin']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-admin']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-admin']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-admin']);
 
         // Course permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-course']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-course']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-course']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-course']);
 
         // Parent permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-parent']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-parent']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-parent']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-parent']);
 
         // Teacher permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-teacher']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-teacher']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-teacher']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-teacher']);
 
         // Department permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-department']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-department']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-department']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-department']);
 
         // Education level permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-level']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-level']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-level']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-level']);
 
         // App administration permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-app-admin']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-app-admin']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-app-admin']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-app-admin']);
 
         // Event permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-event']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-event']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-event']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-event']);
 
         // Exam permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-exam']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-exam']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-exam']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-exam']);
 
         // Exam timetable permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-exam-timetable']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-exam-timetable']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-exam-timetable']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-exam-timetable']);
 
         // Exam type permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-exam-type']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-exam-type']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-exam-type']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-exam-type']);
 
         // Fee payment permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-fee-payment']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-fee-payment']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-fee-payment']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-fee-payment']);
 
         // Grade permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-grade']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-grade']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-grade']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-grade']);
 
         // Report card permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-report-card']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-report-card']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-report-card']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-report-card']);
 
         // Resit courses permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-resit-course']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-resit-course']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-resit-course']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-resit-course']);
 
         // Resit exam timetable permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-resit-exam-timetable']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-resit-exam-timetable']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-resit-exam-timetable']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-resit-exam-timetable']);
 
         // School permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-school']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-school']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-school']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-school']);
 
         // School branch permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-school-branch']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-school-branch']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-school-branch']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-school-branch']);
 
         // School expenses permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-school-expense']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-school-expense']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-school-expense']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-school-expense']);
 
         // Semester permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-semester']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-semester']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-semester']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-semester']);
 
         // Specialty permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-specialty']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-specialty']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-specialty']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-specialty']);
 
         // Student batch permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-student-batch']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-student-batch']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-student-batch']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-student-batch']);
 
         // Specialty time-table permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-specialty-time-table']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-specialty-time-table']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-specialty-time-table']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-specialty-time-table']);
 
         // Student resit permissions
         Permission::create(['id' =>  Str::uuid(),  'name' => 'create-student-resit']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'edit-student-resit']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'view-student-resit']);
         Permission::create(['id' =>  Str::uuid(),  'name' => 'delete-student-resit']);
    }
}
