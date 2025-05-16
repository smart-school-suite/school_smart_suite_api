<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\PermissionCategory;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class permissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*[guard]-[module]-[action]*/

        //student Permissions
        try {
            DB::beginTransaction();
            $this->command->info("Permission Seeding has begun");
            $studentMananger = PermissionCategory::where("title", "Student Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.student.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.activate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.deactivate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.mark.student.as.dropout",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.reinstate.dropout.student",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.view.student.dropout",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.delete.student.dropout",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.view.student.details",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.view.students",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.delete.student",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.promote",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentMananger->id
            ]);

            //student batch mananger permissions
            $studentBatchMananger = PermissionCategory::where("title", "Student Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.student.batch.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentBatchMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.batch.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentBatchMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.batch.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentBatchMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.batch.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentBatchMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.batch.view.graduation.dates",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentBatchMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.batch.create.graduation.dates",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentBatchMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.batch.activate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentBatchMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.student.batch.deactivate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $studentBatchMananger->id
            ]);

            //specailty Permissions
            $specialtyMananger = PermissionCategory::where("title", "Specialty Manager")->firstOrFail();

            Permission::create([
                'name' => "schoolAdmin.specialty.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $specialtyMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.specialty.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $specialtyMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.specialty.show.details",
                "guard_name" => "schooladmin",
                "permission_category_id" => $specialtyMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.specialty.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $specialtyMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.specialty.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $specialtyMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.specialty.activate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $specialtyMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.specialty.deactivate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $specialtyMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.specialty.timetable.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $specialtyMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.specialty.timetable.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $specialtyMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.specialty.timetable.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $specialtyMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.specialty.timetable.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $specialtyMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.specialty.timetable.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $specialtyMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.specialty.timetable.avialability.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $specialtyMananger->id
            ]);

            //hos permissions
            $hosMananger = PermissionCategory::where("title", "HOS Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.hos.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $hosMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.hos.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $hosMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.hos.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $hosMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.hos.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $hosMananger->id
            ]);

            //teacher permission
            $teacherMananager = PermissionCategory::where("title", "Teacher Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.teacher.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.teacher.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.teacher.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.teacher.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.teacher.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.teacher.view.time.timetable",
                "guard_name" => "schooladmin",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.teacher.add.specialty.peference",
                "guard_name" => "schooladmin",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.teacher.view.specialty.peference",
                "guard_name" => "schooladmin",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "teacher.teacher.view.specialty.peference",
                "guard_name" => "teacher",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.teacher.activate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.teacher.deactivate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $teacherMananager->id
            ]);

            //availability Permissions Starts here
            Permission::create([
                'name' => "teacher.avialability.view",
                "guard_name" => "teacher",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.avialability.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "teacher.avialability.create",
                "guard_name" => "teacher",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "teacher.avialability.show.teacher",
                "guard_name" => "teacher",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "teacher.avialability.delete",
                "guard_name" => "teacher",
                "permission_category_id" => $teacherMananager->id
            ]);
            Permission::create([
                'name' => "teacher.avialability.update",
                "guard_name" => "teacher",
                "permission_category_id" => $teacherMananager->id
            ]);

            //Exam Manager
            $examMananger = PermissionCategory::where("title", "Exam Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.exam.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.exam.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.exam.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.exam.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.exam.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "student.exam.show",
                "guard_name" => "student",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "teacher.exam.show",
                "guard_name" => "teacher",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.exam.add.grade.config",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.exam.view.accessed.student",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.exam.view.letter.grades",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.exam.candidate.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.exam.candidate.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.exam.timetable.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.exam.timetable.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.exam.timetable.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.exam.timetable.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.exam.timetable.course.data",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            //exam results

            Permission::create([
                'name' => "schoolAdmin.examResults.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.examResults.view.student",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "teacher.examResults.view.student",
                "guard_name" => "teacher",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "student.examResults.view.student",
                "guard_name" => "student",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.examResults.view.standings",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);

            //exam permissions
            Permission::create([
                'name' => "appAdmin.examType.create",
                "guard_name" => "appAdmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "appAdmin.examType.update",
                "guard_name" => "appAdmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "appAdmin.examType.view",
                "guard_name" => "appAdmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.examType.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "appAdmin.examType.delete",
                "guard_name" => "appAdmin",
                "permission_category_id" => $examMananger->id
            ]);

            //resit exam Mananger
            Permission::create([
                'name' => "schoolAdmin.resitExam.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.resitExam.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.resitExam.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.resitExam.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.resitExam.add.grading",
                "guard_name" => "schooladmin",
                "permission_category_id" => $examMananger->id
            ]);

            $electionMananger = PermissionCategory::where("title", "Election Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.election.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.election.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.election.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.election.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.election.view.candidates",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.election.add.participants",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.election.view.participants",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.election.vote",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.election.view.results",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.election.view.past.winners",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.election.view.winners.current",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);

            //election type permissions
            Permission::create([
                'name' => "schoolAdmin.electionType.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionType.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionType.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionType.activate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionType.deactivate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionType.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionType.view.active",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);

            //election Applications
            Permission::create([
                'name' => "schoolAdmin.electionApplications.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "student.electionApplications.create",
                "guard_name" => "student",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionApplications.view.elections",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "student.electionApplications.update",
                "guard_name" => "student",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionApplications.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionApplications.approve",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);

            //electionTypes Permissions
            Permission::create([
                'name' => "schoolAdmin.electionRole.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionRole.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "student.electionRole.view.election",
                "guard_name" => "student",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionRole.view.election",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "student.electionRole.view.active.election",
                "guard_name" => "student",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionRole.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionRole.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionRole.activate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.electionRole.deactivate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $electionMananger->id
            ]);

            $resitMananger = PermissionCategory::where("title", "Resit Manager")->firstOrFail();
            Permission::create([
                'name' => "student.studentResits.view.student",
                "guard_name" => "student",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.view.student",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.pay",
                "guard_name" => "student",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.transactions.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.transactions.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.transaction.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.transaction.reverse",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.store.scores",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.update.scores",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.view.evaluation.data",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.view.student.resitExam",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.view.eligable.student",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.studentResits.view.eligable.student.resitExam",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.timetable.resitexam.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.timetable.resitexam.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.timetable.resitexam.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.timetable.resitexam.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.timetable.resitexam.courses.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $resitMananger->id
            ]);
            $courseMananger = PermissionCategory::where("title", "Course Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.course.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $courseMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.course.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $courseMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.course.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $courseMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.course.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $courseMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.course.view.active",
                "guard_name" => "schooladmin",
                "permission_category_id" => $courseMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.course.activate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $courseMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.course.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $courseMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.course.deactivate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $courseMananger->id
            ]);

            $countryMananger = PermissionCategory::where("title", "Country Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.country.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $countryMananger->id
            ]);
            Permission::create([
                'name' => "appAdmin.country.update",
                "guard_name" => "appAdmin",
                "permission_category_id" => $countryMananger->id
            ]);
            Permission::create([
                'name' => "appAdmin.country.delete",
                "guard_name" => "appAdmin",
                "permission_category_id" => $countryMananger->id
            ]);
            Permission::create([
                'name' => "appAdmin.country.create",
                "guard_name" => "appAdmin",
                "permission_category_id" => $countryMananger->id
            ]);

            $departmentMananger = PermissionCategory::where("title", "Department Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.department.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $departmentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.department.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $departmentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.department.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $departmentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.department.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $departmentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.department.activate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $departmentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.department.deactivate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $departmentMananger->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.department.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $departmentMananger->id
            ]);

            $hodManager = PermissionCategory::where("title", "HOD Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.hod.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $hodManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.hod.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $hodManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.hod.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $hodManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.hod.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $hodManager->id
            ]);

            $eventManager = PermissionCategory::where("title", "Event Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.event.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $eventManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.event.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $eventManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.event.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $eventManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.event.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $eventManager->id
            ]);
            Permission::create([
                'name' => "student.event.view",
                "guard_name" => "student",
                "permission_category_id" => $eventManager->id
            ]);
            Permission::create([
                'name' => "teacher.event.view",
                "guard_name" => "teacher",
                "permission_category_id" => $eventManager->id
            ]);
            Permission::create([
                'name' => "teacher.event.show",
                "guard_name" => "teacher",
                "permission_category_id" => $eventManager->id
            ]);
            Permission::create([
                'name' => "student.event.show",
                "guard_name" => "student",
                "permission_category_id" => $eventManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.event.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $eventManager->id
            ]);

            $additionalFeeManager = PermissionCategory::where("title", "Additional Fee Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.additionalFee.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $additionalFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.additionalFee.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $additionalFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.additionalFee.view.student",
                "guard_name" => "schooladmin",
                "permission_category_id" => $additionalFeeManager->id
            ]);
            Permission::create([
                'name' => "student.additionalFee.view.student",
                "guard_name" => "student",
                "permission_category_id" => $additionalFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.additionalFee.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $additionalFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.additionalFee.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $additionalFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.additionalFee.pay",
                "guard_name" => "schooladmin",
                "permission_category_id" => $additionalFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.additionalFee.transactions.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $additionalFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.additionalFee.transactions.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $additionalFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.additionalFee.transactions.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $additionalFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.additionalFee.transactions.reverse",
                "guard_name" => "schooladmin",
                "permission_category_id" => $additionalFeeManager->id
            ]);

            //additional Fee Category Permissions
            Permission::create([
                'name' => "schoolAdmin.additionalFeeCategory.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $additionalFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.additionalFeeCategory.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $additionalFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.additionalFeeCategory.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $additionalFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.additionalFeeCategory.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $additionalFeeManager->id
            ]);

            $tuitionFeeManager = PermissionCategory::where("title", "Tuition Fee Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.tuitionFee.pay",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.tuitionFee.view.paid",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.tuitionFee.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.tuitionFee.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.tuitionFee.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.tuitionFee.view.deptors",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.tuitionFee.view.transactions",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.tuitionFee.show.transaction",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.tuitionFee.reverse.transaction",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.tuitionFee.delete.transaction",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);

            $registrationFeeManager = PermissionCategory::where("title", "Registration Fee Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.registrationFee.pay",
                "guard_name" => "schooladmin",
                "permission_category_id" => $registrationFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.registrationFee.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $registrationFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.registrationFee.view.transaction",
                "guard_name" => "schooladmin",
                "permission_category_id" => $registrationFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.registrationFee.delete.transaction",
                "guard_name" => "schooladmin",
                "permission_category_id" => $registrationFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.registrationFee.reverse.transaction",
                "guard_name" => "schooladmin",
                "permission_category_id" => $registrationFeeManager->id
            ]);

            //Fee Payment Schedule Permissions
            Permission::create([
                'name' => "schoolAdmin.feeSchedule.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.feeSchedule.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.feeSchedule.view.specialty",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.feeSchedule.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.feeSchedule.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);

            //Fee Waiver Permissions
            Permission::create([
                'name' => "schoolAdmin.feeWaiver.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.feeWaiver.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.feeWaiver.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.feeWaiver.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.feeWaiver.view.student",
                "guard_name" => "schooladmin",
                "permission_category_id" => $tuitionFeeManager->id
            ]);


            //grade permissions
            $gradeManager = PermissionCategory::where("title", "Grades Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.grades.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $gradeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.grades.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $gradeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.grades.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $gradeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.grades.update.grade.config",
                "guard_name" => "schooladmin",
                "permission_category_id" => $gradeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.grades.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $gradeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.grades.relatedexam.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $gradeManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.grades.config.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $gradeManager->id
            ]);

            //grade category
            Permission::create([
                'name' => "appAdmin.gradesCategory.create",
                "guard_name" => "appAdmin",
                "permission_category_id" => $gradeManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.gradesCategory.delete",
                "guard_name" => "appAdmin",
                "permission_category_id" => $gradeManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.gradesCategory.update",
                "guard_name" => "appAdmin",
                "permission_category_id" => $gradeManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.gradesCategory.view",
                "guard_name" => "appAdmin",
                "permission_category_id" => $gradeManager->id
            ]);

            //letter grade manager
            $letterGradeManager = PermissionCategory::where("title", "Letter Grade Manager")->firstOrFail();
            Permission::create([
                'name' => "appAdmin.letterGrade.create",
                "guard_name" => "appAdmin",
                "permission_category_id" => $letterGradeManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.letterGrade.update",
                "guard_name" => "appAdmin",
                "permission_category_id" => $letterGradeManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.letterGrade.delete",
                "guard_name" => "appAdmin",
                "permission_category_id" => $letterGradeManager->id
            ]);
            //create level manager
            $levelManager = PermissionCategory::where("title", "Level Manager")->firstOrFail();
            Permission::create([
                'name' => "appAdmin.level.create",
                "guard_name" => "appAdmin",
                "permission_category_id" => $levelManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.level.update",
                "guard_name" => "appAdmin",
                "permission_category_id" => $levelManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.level.delete",
                "guard_name" => "appAdmin",
                "permission_category_id" => $levelManager->id
            ]);

            //semester Manager
            $semesterManager = PermissionCategory::where("title", "Semester Manager")->firstOrFail();
            Permission::create([
                'name' => "appAdmin.semester.create",
                "guard_name" => "appAdmin",
                "permission_category_id" => $semesterManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.semester.delete",
                "guard_name" => "appAdmin",
                "permission_category_id" => $semesterManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.semester.update",
                "guard_name" => "appAdmin",
                "permission_category_id" => $semesterManager->id
            ]);

            //school Semester
            Permission::create([
                'name' => "schoolAdmin.schoolSemester.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $semesterManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolSemester.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $semesterManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolSemester.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $semesterManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolSemester.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $semesterManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolSemester.view.active",
                "guard_name" => "schooladmin",
                "permission_category_id" => $semesterManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolSemester.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $semesterManager->id
            ]);

            $ratesManager = PermissionCategory::where("title", "Rate Card Manager")->firstOrFail();
            Permission::create([
                'name' => "appAdmin.rateCard.create",
                "guard_name" => "appAdmin",
                "permission_category_id" => $ratesManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.rateCard.delete",
                "guard_name" => "appAdmin",
                "permission_category_id" => $ratesManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.rateCard.update",
                "guard_name" => "appAdmin",
                "permission_category_id" => $ratesManager->id
            ]);

            $subscriptionManager = PermissionCategory::where("title", "Subscription Manager")->firstOrFail();
            Permission::create([
                'name' => "appAdmin.subscription.view.subscribed.schools",
                "guard_name" => "appAdmin",
                "permission_category_id" => $subscriptionManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.subscription.show.schoolBranch",
                "guard_name" => "schooladmin",
                "permission_category_id" => $subscriptionManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.subscription.delete.transaction",
                "guard_name" => "schooladmin",
                "permission_category_id" => $subscriptionManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.subscription.view.transactions.schoolBranch",
                "guard_name" => "schooladmin",
                "permission_category_id" => $subscriptionManager->id
            ]);

            $schoolExpensesManager = PermissionCategory::where("title", "School Expenses Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.schoolExpenses.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolExpensesManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolExpenses.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolExpensesManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolExpenses.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolExpensesManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolExpenses.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolExpensesManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolExpenses.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolExpensesManager->id
            ]);

            //expenses category permissions
            Permission::create([
                'name' => "schoolAdmin.schoolExpenses.category.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolExpensesManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolExpenses.category.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolExpensesManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolExpenses.category.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolExpensesManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolExpenses.category.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolExpensesManager->id
            ]);

            $schoolAdminManager = PermissionCategory::where("title", "School Admin Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.schoolAdmin.create",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolAdminManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolAdmin.update",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolAdminManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolAdmin.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolAdminManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolAdmin.view",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolAdminManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolAdmin.show",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolAdminManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolAdmin.upload.avatar",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolAdminManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolAdmin.delete.avatar",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolAdminManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolAdmin.deactivate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolAdminManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolAdmin.activate",
                "guard_name" => "schooladmin",
                "permission_category_id" => $schoolAdminManager->id
            ]);

            $schoolBranchManager = PermissionCategory::where("title", "School Branch Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.schoolBranch.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $schoolBranchManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.schoolBranch.update",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $schoolBranchManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.schoolBranch.view",
                "guard_name" => "appAdmin",
                "permission_category_id" =>  $schoolBranchManager->id
            ]);

            $schoolManager = PermissionCategory::where("title", "School Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.school.show",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $schoolManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.school.update",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $schoolManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.school.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $schoolManager->id
            ]);

            $roleManager = PermissionCategory::where("title", "Role Manager")->firstOrFail();
            Permission::create([
                'name' => "appAdmin.role.delete",
                "guard_name" => "appAdmin",
                "permission_category_id" =>  $roleManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.role.update",
                "guard_name" => "appAdmin",
                "permission_category_id" =>  $roleManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.role.create",
                "guard_name" => "appAdmin",
                "permission_category_id" =>  $roleManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.role.assign",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $roleManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.role.remove",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $roleManager->id
            ]);

            $permissionManager = PermissionCategory::where("title", "Permission Manager")->firstOrFail();
            Permission::create([
                'name' => "appAdmin.permission.delete",
                "guard_name" => "appAdmin",
                "permission_category_id" =>  $permissionManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.permission.create",
                "guard_name" => "appAdmin",
                "permission_category_id" =>  $permissionManager->id
            ]);
            Permission::create([
                'name' => "appAdmin.permission.update",
                "guard_name" => "appAdmin",
                "permission_category_id" =>  $permissionManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.permission.view.schoolAdmin",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $permissionManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.permission.assign",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $permissionManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.permission.remove",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $permissionManager->id
            ]);

            $parentManager = PermissionCategory::Where("title", "Parent Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.parent.create",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $parentManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.parent.update",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $parentManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.parent.view",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $parentManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.parent.delete",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $parentManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.parent.show",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $parentManager->id
            ]);


            $marksManager = PermissionCategory::where("title", "Marks Manager")->firstOrFail();
            Permission::create([
                'name' => "schoolAdmin.mark.create.ca.marks",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $marksManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.mark.update.ca.marks",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $marksManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.mark.create.exam.marks",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $marksManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.mark.update.exam.marks",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $marksManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.mark.view.accessed.courses",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $marksManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.mark.view.ca.evaluation.data",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $marksManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.mark.view.exam.evaluation.data",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $marksManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.mark.view.ca.result.data",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $marksManager->id
            ]);
            Permission::create([
                'name' => "schoolAdmin.mark.view.student",
                "guard_name" => "schooladmin",
                "permission_category_id" =>  $marksManager->id
            ]);
            DB::commit();
            $this->command->info("Permissions Created Successfully");
        } catch (Exception $e) {
            DB::rollback();
            $this->command->info($e->getMessage());
        }
    }
}
