<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\PermissionCategory;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $this->studentManagerPermissions();
        $this->studentBatchManagerPermissions();
        $this->specialtyManagerPermissions();
        $this->teacherManagerPermissions();
        $this->examManagerPermissions();
        $this->electionManagerPermissions();
        $this->resitManagerPermissions();
        $this->courseManagerPermissions();
        $this->countryManagerPermissions();
        $this->departmentManagerPermissions();
        $this->schoolEventManagerPermissions();
        $this->additionalFeeManagerPermissions();
        $this->tuitionFeeManagerPermissions();
        $this->registrationFeeManagerPermissions();
        $this->gradesManagerPermissions();
        $this->letterGradeManagerPermissions();
        $this->levelManagerPermissions();
        $this->semesterManagerPermissions();
        $this->ratesManagerPermissions();
        $this->subscriptionManagerPermissions();
        $this->schoolExpensesManagerPermissions();
        $this->schoolAdminManagerPermissions();
        $this->schoolBranchManagerPermissions();
        $this->schoolManagerPermissions();
        $this->roleManagerPermissions();
        $this->parentManagerPermissions();
        $this->appManagerPermissions();
        $this->marksManagerPermissions();
        $this->anouncementCategoryPermission();
        $this->anouncementPermission();
        $this->additionalFeeCategoryManagerPermissions();
        $this->schoolEventCategoryManagerPermission();
        $this->teacherCoursePreferenceManagementPermission();
        $this->schoolAppSettingPermission();
        $this->hallPermission();
        $this->resitManager();
    }

    private function studentManagerPermissions(): void
    {
        $studentMananger = PermissionCategory::where("title", "Student Manager")->firstOrFail();

        $permissions = [
            [
                'name' => "schoolAdmin.student.create",
                'desc_name' => "Create Student",
                'desc_text' => "Manages Student Creation",
            ],
            [
                'name' => "schoolAdmin.student.update",
                'desc_name' => "Update Student",
                'desc_text' => "Manages Student Updates",
            ],
            ['name' => "schoolAdmin.student.activate"],
            ['name' => "schoolAdmin.student.deactivate"],
            ['name' => "schoolAdmin.student.mark.student.as.dropout"],
            ['name' => "schoolAdmin.student.reinstate.dropout.student"],
            ['name' => "schoolAdmin.student.view.student.dropout"],
            ['name' => "schoolAdmin.student.delete.student.dropout"],
            ['name' => "schoolAdmin.student.view.student.details"],
            ['name' => "schoolAdmin.student.view.students"],
            ['name' => "schoolAdmin.student.delete.student"],
            ['name' => "schoolAdmin.student.promote"],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                [
                    'name'       => $permission['name'],
                    'guard_name' => 'schooladmin',
                ],
                [
                    'permission_category_id' => $studentMananger->id,
                    'desc_name'              => $permission['desc_name'] ?? null,
                    'desc_text'              => $permission['desc_text'] ?? null,
                ]
            );
        }
    }
    private function studentBatchManagerPermissions(): void
    {
        $studentBatchMananger = PermissionCategory::where("title", "Student Manager")->firstOrFail();

        $permissions = [
            "schoolAdmin.student.batch.create",
            "schoolAdmin.student.batch.update",
            "schoolAdmin.student.batch.delete",
            "schoolAdmin.student.batch.view",
            "schoolAdmin.student.batch.view.graduation.dates",
            "schoolAdmin.student.batch.create.graduation.dates",
            "schoolAdmin.student.batch.activate",
            "schoolAdmin.student.batch.deactivate",
        ];

        foreach ($permissions as $name) {
            Permission::updateOrCreate(
                [
                    'name'       => $name,
                    'guard_name' => 'schooladmin',
                ],
                [
                    'permission_category_id' => $studentBatchMananger->id,
                ]
            );
        }
    }


    private function specialtyManagerPermissions(): void
    {
        $specialtyMananger = PermissionCategory::where("title", "Specialty Manager")->firstOrFail();

        $permissions = [
            "schoolAdmin.specialty.create",
            "schoolAdmin.specialty.view",
            "schoolAdmin.specialty.show.details",
            "schoolAdmin.specialty.update",
            "schoolAdmin.specialty.delete",
            "schoolAdmin.specialty.activate",
            "schoolAdmin.specialty.deactivate",
            "schoolAdmin.specialty.timetable.create",
            "schoolAdmin.specialty.timetable.update",
            "schoolAdmin.specialty.timetable.view",
            "schoolAdmin.specialty.timetable.show",
            "schoolAdmin.specialty.timetable.delete",
            "schoolAdmin.specialty.timetable.avialability.view",
        ];

        foreach ($permissions as $name) {
            Permission::updateOrCreate(
                [
                    'name'       => $name,
                    'guard_name' => 'schooladmin',
                ],
                [
                    'permission_category_id' => $specialtyMananger->id,
                ]
            );
        }
    }
    private function teacherManagerPermissions(): void
    {
        //teacher permission
        $teacherMananager = PermissionCategory::where("title", "Teacher Manager")->firstOrFail();

        $permissions = [
            // schooladmin guard
            ['name' => "schoolAdmin.teacher.view",                      'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.teacher.create",                    'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.teacher.show",                      'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.teacher.update",                    'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.teacher.delete",                    'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.teacher.view.time.timetable",       'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.teacher.add.specialty.peference",   'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.teacher.view.specialty.peference",  'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.teacher.activate",                  'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.teacher.deactivate",                'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.avialability.view",                 'guard_name' => 'schooladmin'],

            // teacher guard
            ['name' => "teacher.teacher.view.specialty.peference",      'guard_name' => 'teacher'],
            ['name' => "teacher.avialability.view",                     'guard_name' => 'teacher'],
            ['name' => "teacher.avialability.create",                   'guard_name' => 'teacher'],
            ['name' => "teacher.avialability.show.teacher",             'guard_name' => 'teacher'],
            ['name' => "teacher.avialability.delete",                   'guard_name' => 'teacher'],
            ['name' => "teacher.avialability.update",                   'guard_name' => 'teacher'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                [
                    'name'       => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ],
                [
                    'permission_category_id' => $teacherMananager->id,
                ]
            );
        }
    }


    private function examManagerPermissions(): void
    {
        $examMananger = PermissionCategory::where("title", "Exam Manager")->firstOrFail();

        $permissions = [
            // exam
            ['name' => "schoolAdmin.exam.create",                 'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.exam.update",                 'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.exam.view",                   'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.exam.delete",                 'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.exam.show",                   'guard_name' => 'schooladmin'],
            ['name' => "student.exam.show",                       'guard_name' => 'student'],
            ['name' => "teacher.exam.show",                       'guard_name' => 'teacher'],

            // exam grade scale
            ['name' => "schoolAdmin.examGradeScale.add",          'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.examGradeScale.remove",       'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.examGradeScale.view",         'guard_name' => 'schooladmin'],

            // exam candidates
            ['name' => "schoolAdmin.exam.candidate.view",         'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.examCandidate.delete",        'guard_name' => 'schooladmin'],

            // exam timetable
            ['name' => "schoolAdmin.exam.timetable.create",       'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.exam.timetable.update",       'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.exam.timetable.delete",       'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.exam.timetable.view",         'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.exam.timetable.course.data",  'guard_name' => 'schooladmin'],

            // exam results
            ['name' => "schoolAdmin.examResults.view",             'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.examResults.view.student",     'guard_name' => 'schooladmin'],
            ['name' => "teacher.examResults.view.student",         'guard_name' => 'teacher'],
            ['name' => "student.examResults.view.student",         'guard_name' => 'student'],
            ['name' => "schoolAdmin.examResults.view.standings",   'guard_name' => 'schooladmin'],

            // exam types
            ['name' => "appAdmin.examType.create",                 'guard_name' => 'appAdmin'],
            ['name' => "appAdmin.examType.update",                 'guard_name' => 'appAdmin'],
            ['name' => "appAdmin.examType.view",                   'guard_name' => 'appAdmin'],
            ['name' => "schoolAdmin.examType.view",                'guard_name' => 'schooladmin'],
            ['name' => "appAdmin.examType.delete",                 'guard_name' => 'appAdmin'],

            // exam evaluation
            ['name' => "schoolAdmin.examEvaluation.addScore",      'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.examEvaluation.updateScore",   'guard_name' => 'schooladmin'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                [
                    'name'       => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ],
                [
                    'permission_category_id' => $examMananger->id,
                ]
            );
        }
    }
    private function electionManagerPermissions(): void
    {
        $electionMananger = PermissionCategory::where("title", "Election Manager")->firstOrFail();

        $permissions = [
            // election
            ['name' => "schoolAdmin.election.create",                         'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.election.view",                           'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.election.delete",                         'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.election.update",                         'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.election.add.participants",               'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.election.view.participants",              'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.election.vote",                           'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.election.view.results",                   'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.election.view.past.winners",              'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.election.view.winners.current",           'guard_name' => 'schooladmin'],

            // election candidates
            ['name' => "schoolAdmin.electionCandidate.view",                  'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.electionCandidate.disqualify",            'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.electionCandidate.reinstate",             'guard_name' => 'schooladmin'],

            // election types
            ['name' => "schoolAdmin.electionType.create",                     'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.electionType.update",                     'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.electionType.view",                       'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.electionType.activate",                   'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.electionType.deactivate",                 'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.electionType.delete",                     'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.electionType.view.active",                'guard_name' => 'schooladmin'],

            // election applications
            ['name' => "schoolAdmin.electionApplications.view",               'guard_name' => 'schooladmin'],
            ['name' => "student.electionApplications.create",                 'guard_name' => 'student'],
            ['name' => "schoolAdmin.electionApplications.view.elections",     'guard_name' => 'schooladmin'],
            ['name' => "student.electionApplications.update",                 'guard_name' => 'student'],
            ['name' => "schoolAdmin.electionApplications.delete",             'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.electionApplications.approve",            'guard_name' => 'schooladmin'],

            // election roles
            ['name' => "schoolAdmin.electionRole.create",                     'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.electionRole.view",                       'guard_name' => 'schooladmin'],
            ['name' => "student.electionRole.view.election",                  'guard_name' => 'student'],
            ['name' => "schoolAdmin.electionRole.view.election",              'guard_name' => 'schooladmin'],
            ['name' => "student.electionRole.view.active.election",           'guard_name' => 'student'],
            ['name' => "schoolAdmin.electionRole.update",                     'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.electionRole.delete",                     'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.electionRole.activate",                   'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.electionRole.deactivate",                 'guard_name' => 'schooladmin'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                [
                    'name'       => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ],
                [
                    'permission_category_id' => $electionMananger->id,
                ]
            );
        }
    }
    private function resitManagerPermissions(): void
    {
        $resitMananger = PermissionCategory::where("title", "Resit Manager")->firstOrFail();

        $permissions = [
            ['name' => "student.studentResits.view.student",                        'guard_name' => 'student'],
            ['name' => "schoolAdmin.studentResits.view.student",                    'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.studentResits.pay",                             'guard_name' => 'student'],
            ['name' => "schoolAdmin.studentResits.update",                          'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.studentResits.delete",                          'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.studentResits.view",                            'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.studentResits.show",                            'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.studentResits.transactions.view",               'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.studentResits.transactions.delete",             'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.studentResits.transaction.show",                'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.studentResits.transaction.reverse",             'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.studentResits.store.scores",                    'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.studentResits.update.scores",                   'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.studentResits.view.evaluation.data",            'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.studentResits.view.student.resitExam",          'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.studentResits.view.eligable.student",           'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.studentResits.view.eligable.student.resitExam", 'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.timetable.resitexam.create",                    'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.timetable.resitexam.update",                    'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.timetable.resitexam.delete",                    'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.timetable.resitexam.view",                      'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.timetable.resitexam.courses.view",              'guard_name' => 'schooladmin'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                [
                    'name'       => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ],
                [
                    'permission_category_id' => $resitMananger->id,
                ]
            );
        }
    }
    private function courseManagerPermissions(): void
    {
        $courseMananger = PermissionCategory::where("title", "Course Manager")->firstOrFail();

        $permissions = [
            "schoolAdmin.course.create",
            "schoolAdmin.course.view",
            "schoolAdmin.course.update",
            "schoolAdmin.course.delete",
            "schoolAdmin.course.view.active",
            "schoolAdmin.course.activate",
            "schoolAdmin.course.show",
            "schoolAdmin.course.deactivate",
        ];

        foreach ($permissions as $name) {
            Permission::updateOrCreate(
                [
                    'name'       => $name,
                    'guard_name' => 'schooladmin',
                ],
                [
                    'permission_category_id' => $courseMananger->id,
                ]
            );
        }
    }


    private function teacherCoursePreferenceManagementPermission(): void
    {
        $courseMananger = PermissionCategory::where("title", "Teacher Course Preference Manager")->firstOrFail();

        $permissions = [
            "schoolAdmin.teacherCoursePreference.assign",
            "schoolAdmin.teacherCoursePreference.remove",
            "schoolAdmin.teacherCoursePreference.view",
        ];

        foreach ($permissions as $name) {
            Permission::updateOrCreate(
                [
                    'name'       => $name,
                    'guard_name' => 'schooladmin',
                ],
                [
                    'permission_category_id' => $courseMananger->id,
                ]
            );
        }
    }
    private function countryManagerPermissions(): void
    {
        $countryMananger = PermissionCategory::where("title", "Country Manager")->firstOrFail();

        $permissions = [
            ['name' => "schoolAdmin.country.view",  'guard_name' => 'schooladmin'],
            ['name' => "appAdmin.country.update",   'guard_name' => 'appAdmin'],
            ['name' => "appAdmin.country.delete",   'guard_name' => 'appAdmin'],
            ['name' => "appAdmin.country.create",   'guard_name' => 'appAdmin'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                [
                    'name'       => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ],
                [
                    'permission_category_id' => $countryMananger->id,
                ]
            );
        }
    }


    private function departmentManagerPermissions(): void
    {
        $departmentMananger = PermissionCategory::where("title", "Department Manager")->firstOrFail();

        $permissions = [
            "schoolAdmin.department.create",
            "schoolAdmin.department.update",
            "schoolAdmin.department.delete",
            "schoolAdmin.department.view",
            "schoolAdmin.department.activate",
            "schoolAdmin.department.deactivate",
            "schoolAdmin.department.show",
        ];

        foreach ($permissions as $name) {
            Permission::updateOrCreate(
                [
                    'name'       => $name,
                    'guard_name' => 'schooladmin',
                ],
                [
                    'permission_category_id' => $departmentMananger->id,
                ]
            );
        }
    }
    private function schoolEventManagerPermissions(): void
    {
        $eventManager = PermissionCategory::where("title", "Event Manager")->firstOrFail();

        $permissions = [
            ['name' => "schoolAdmin.event.create", 'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.event.update", 'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.event.delete", 'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.event.view",   'guard_name' => 'schooladmin'],
            ['name' => "student.event.view",       'guard_name' => 'student'],
            ['name' => "teacher.event.view",       'guard_name' => 'teacher'],
            ['name' => "teacher.event.show",       'guard_name' => 'teacher'],
            ['name' => "student.event.show",       'guard_name' => 'student'],
            ['name' => "schoolAdmin.event.show",   'guard_name' => 'schooladmin'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                [
                    'name'       => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ],
                [
                    'permission_category_id' => $eventManager->id,
                ]
            );
        }
    }
    private function schoolEventCategoryManagerPermission(): void
    {
        $eventManager = PermissionCategory::where("title", "Event Category Manager")->firstOrFail();

        $permissions = [
            "schoolAdmin.eventCategory.create",
            "schoolAdmin.eventCategory.update",
            "schoolAdmin.eventCategory.delete",
            "schoolAdmin.eventCategory.view",
            "schoolAdmin.eventCategory.activate",
            "schoolAdmin.eventCategory.deactivate",
        ];

        foreach ($permissions as $name) {
            Permission::updateOrCreate(
                [
                    'name'       => $name,
                    'guard_name' => 'schooladmin',
                ],
                [
                    'permission_category_id' => $eventManager->id,
                ]
            );
        }
    }



    private function additionalFeeManagerPermissions(): void
    {
        $additionalFeeManager = PermissionCategory::where("title", "Additional Fee Manager")->firstOrFail();

        $permissions = [
            ['name' => "schoolAdmin.additionalFee.create",                     'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.additionalFee.view",                       'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.additionalFee.view.student",               'guard_name' => 'schooladmin'],
            ['name' => "student.additionalFee.view.student",                   'guard_name' => 'student'],
            ['name' => "schoolAdmin.additionalFee.update",                     'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.additionalFee.delete",                     'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.additionalFee.pay",                        'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.additionalFee.transactions.view",          'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.additionalFee.transactions.show",          'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.additionalFee.transactions.delete",        'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.additionalFee.transactions.reverse",       'guard_name' => 'schooladmin'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                [
                    'name'       => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ],
                [
                    'permission_category_id' => $additionalFeeManager->id,
                ]
            );
        }
    }
    private function additionalFeeCategoryManagerPermissions(): void
    {
        $additionalFeeManager = PermissionCategory::where("title", "Additional Fee Category Manager")->firstOrFail();

        $permissions = [
            "schoolAdmin.additionalFeeCategory.create",
            "schoolAdmin.additionalFeeCategory.update",
            "schoolAdmin.additionalFeeCategory.delete",
            "schoolAdmin.additionalFeeCategory.view",
        ];

        foreach ($permissions as $name) {
            Permission::updateOrCreate(
                [
                    'name'       => $name,
                    'guard_name' => 'schooladmin',
                ],
                [
                    'permission_category_id' => $additionalFeeManager->id,
                ]
            );
        }
    }
    private function tuitionFeeManagerPermissions(): void
    {
        $tuitionFeeManager = PermissionCategory::where("title", "Tuition Fee Manager")->firstOrFail();

        $permissions = [
            // Tuition fees
            "schoolAdmin.tuitionFee.pay",
            "schoolAdmin.tuitionFee.view.paid",
            "schoolAdmin.tuitionFee.view",
            "schoolAdmin.tuitionFee.update",
            "schoolAdmin.tuitionFee.delete",
            "schoolAdmin.tuitionFee.view.deptors",
            "schoolAdmin.tuitionFee.view.transactions",
            "schoolAdmin.tuitionFee.show.transaction",
            "schoolAdmin.tuitionFee.reverse.transaction",
            "schoolAdmin.tuitionFee.delete.transaction",

            // Fee Payment Schedule Slots
            "schoolAdmin.feeScheduleSlots.create",
            "schoolAdmin.feeScheduleSlots.view",
            "schoolAdmin.feeScheduleSlots.delete",
            "schoolAdmin.feeScheduleSlot.update",

            // Fee Payment Schedule
            "schoolAdmin.feeSchedule.create",
            "schoolAdmin.feeSchedule.view",
            "schoolAdmin.feeSchedule.view.specialty",
            "schoolAdmin.feeSchedule.update",
            "schoolAdmin.feeSchedule.delete",

            // Fee Waivers
            "schoolAdmin.feeWaiver.create",
            "schoolAdmin.feeWaiver.view",
            "schoolAdmin.feeWaiver.update",
            "schoolAdmin.feeWaiver.delete",
            "schoolAdmin.feeWaiver.view.student",
        ];

        foreach ($permissions as $name) {
            Permission::updateOrCreate(
                [
                    'name'       => $name,
                    'guard_name' => 'schooladmin',
                ],
                [
                    'permission_category_id' => $tuitionFeeManager->id,
                ]
            );
        }
    }



    private function registrationFeeManagerPermissions(): void
    {
        $registrationFeeManager = PermissionCategory::where("title", "Registration Fee Manager")->firstOrFail();

        $permissions = [
            "schoolAdmin.registrationFee.pay",
            "schoolAdmin.registrationFee.delete",
            "schoolAdmin.registrationFee.view",
            "schoolAdmin.registrationFee.view.transaction",
            "schoolAdmin.registrationFee.delete.transaction",
            "schoolAdmin.registrationFee.reverse.transaction",
        ];

        foreach ($permissions as $name) {
            Permission::updateOrCreate(
                [
                    'name'       => $name,
                    'guard_name' => 'schooladmin',
                ],
                [
                    'permission_category_id' => $registrationFeeManager->id,
                ]
            );
        }
    }
    private function gradesManagerPermissions(): void
    {
        //grade permissions
        $gradeManager = PermissionCategory::where("title", "Grades Manager")->firstOrFail();

        $permissions = [
            ['name' => "schoolAdmin.grades.create",              'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.grades.view",                'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.grades.update",              'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.grades.update.grade.config", 'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.grades.delete",              'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.grades.relatedexam.view",    'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.grades.config.view",         'guard_name' => 'schooladmin'],

            // grade category
            ['name' => "appAdmin.gradesCategory.create",         'guard_name' => 'appAdmin'],
            ['name' => "appAdmin.gradesCategory.delete",         'guard_name' => 'appAdmin'],
            ['name' => "appAdmin.gradesCategory.update",         'guard_name' => 'appAdmin'],
            ['name' => "appAdmin.gradesCategory.view",           'guard_name' => 'appAdmin'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                [
                    'name'       => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ],
                [
                    'permission_category_id' => $gradeManager->id,
                ]
            );
        }
    }
    private function letterGradeManagerPermissions(): void
    {
        $letterGradeManager = PermissionCategory::where("title", "Letter Grade Manager")->firstOrFail();

        $permissions = [
            "appAdmin.letterGrade.create",
            "appAdmin.letterGrade.update",
            "appAdmin.letterGrade.delete",
        ];

        foreach ($permissions as $name) {
            Permission::updateOrCreate(
                [
                    'name'       => $name,
                    'guard_name' => 'appAdmin',
                ],
                [
                    'permission_category_id' => $letterGradeManager->id,
                ]
            );
        }
    }
    private function levelManagerPermissions(): void
    {
        $levelManager = PermissionCategory::where("title", "Level Manager")->firstOrFail();

        $permissions = [
            "appAdmin.level.create",
            "appAdmin.level.update",
            "appAdmin.level.delete",
        ];

        foreach ($permissions as $name) {
            Permission::updateOrCreate(
                [
                    'name'       => $name,
                    'guard_name' => 'appAdmin',
                ],
                [
                    'permission_category_id' => $levelManager->id,
                ]
            );
        }
    }



    private function semesterManagerPermissions(): void
    {
        $semesterManager = PermissionCategory::where("title", "Semester Manager")->firstOrFail();

        $permissions = [
            ['name' => "appAdmin.semester.create",              'guard_name' => 'appAdmin'],
            ['name' => "appAdmin.semester.delete",              'guard_name' => 'appAdmin'],
            ['name' => "appAdmin.semester.update",              'guard_name' => 'appAdmin'],
            ['name' => "schoolAdmin.schoolSemester.update",     'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.schoolSemester.create",     'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.schoolSemester.delete",     'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.schoolSemester.show",       'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.schoolSemester.view.active", 'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.schoolSemester.view",       'guard_name' => 'schooladmin'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                [
                    'name'       => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ],
                [
                    'permission_category_id' => $semesterManager->id,
                ]
            );
        }
    }
    private function ratesManagerPermissions(): void
    {
        $ratesManager = PermissionCategory::where("title", "Rate Card Manager")->firstOrFail();

        $permissions = [
            "appAdmin.rateCard.create",
            "appAdmin.rateCard.delete",
            "appAdmin.rateCard.update",
        ];

        foreach ($permissions as $name) {
            Permission::updateOrCreate(
                [
                    'name'       => $name,
                    'guard_name' => 'appAdmin',
                ],
                [
                    'permission_category_id' => $ratesManager->id,
                ]
            );
        }
    }
    private function subscriptionManagerPermissions(): void
    {
        $subscriptionManager = PermissionCategory::where("title", "Subscription Manager")->firstOrFail();

        $permissions = [
            ['name' => "appAdmin.subscription.view.subscribed.schools",              'guard_name' => 'appAdmin'],
            ['name' => "schoolAdmin.subscription.show.schoolBranch",                 'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.subscription.delete.transaction",                'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.subscription.view.transactions.schoolBranch",    'guard_name' => 'schooladmin'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                [
                    'name'       => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ],
                [
                    'permission_category_id' => $subscriptionManager->id,
                ]
            );
        }
    }
    private function schoolExpensesManagerPermissions(): void
    {
        $schoolExpensesManager = PermissionCategory::where("title", "School Expenses Manager")->firstOrFail();

        $permissions = [
            "schoolAdmin.schoolExpenses.create",
            "schoolAdmin.schoolExpenses.update",
            "schoolAdmin.schoolExpenses.delete",
            "schoolAdmin.schoolExpenses.view",
            "schoolAdmin.schoolExpenses.show",

            // expenses category permissions
            "schoolAdmin.schoolExpenses.category.create",
            "schoolAdmin.schoolExpenses.category.update",
            "schoolAdmin.schoolExpenses.category.delete",
            "schoolAdmin.schoolExpenses.category.view",
        ];

        foreach ($permissions as $name) {
            Permission::updateOrCreate(
                [
                    'name'       => $name,
                    'guard_name' => 'schooladmin',
                ],
                [
                    'permission_category_id' => $schoolExpensesManager->id,
                ]
            );
        }
    }
    private function schoolAdminManagerPermissions(): void
    {
        $schoolAdminManager = PermissionCategory::where("title", "School Admin Manager")->firstOrFail();

        $permissions = [
            "schoolAdmin.schoolAdmin.create",
            "schoolAdmin.schoolAdmin.update",
            "schoolAdmin.schoolAdmin.delete",
            "schoolAdmin.schoolAdmin.view",
            "schoolAdmin.schoolAdmin.show",
            "schoolAdmin.schoolAdmin.upload.avatar",
            "schoolAdmin.schoolAdmin.delete.avatar",
            "schoolAdmin.schoolAdmin.deactivate",
            "schoolAdmin.schoolAdmin.activate",
            "schoolAdmin.schoolAdmin.permission.view",
            "schoolAdmin.schoolAdmin.permission.revoke",
            "schoolAdmin.schoolAdmin.permission.assign",
            "schoolAdmin.schoolAdmin.role.assign",
            "schoolAdmin.schoolAdmin.role.revoke",
            "schoolAdmin.schoolAdmin.role.view",
        ];

        foreach ($permissions as $name) {
            Permission::updateOrCreate(
                [
                    'name'       => $name,
                    'guard_name' => 'schooladmin',
                ],
                [
                    'permission_category_id' => $schoolAdminManager->id,
                ]
            );
        }
    }




    private function schoolBranchManagerPermissions(): void
    {
        $schoolBranchManager = PermissionCategory::where("title", "School Branch Manager")->firstOrFail();

        $permissions = [
            ['name' => "schoolAdmin.schoolBranch.delete", 'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.schoolBranch.update", 'guard_name' => 'schooladmin'],
            ['name' => "appAdmin.schoolBranch.view",      'guard_name' => 'appAdmin'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                [
                    'name'       => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ],
                [
                    'permission_category_id' => $schoolBranchManager->id,
                ]
            );
        }
    }
    private function schoolManagerPermissions(): void
    {
        $schoolManager = PermissionCategory::where("title", "School Manager")->firstOrFail();

        $permissions = [
            "schoolAdmin.school.show",
            "schoolAdmin.school.update",
            "schoolAdmin.school.delete",
        ];

        foreach ($permissions as $name) {
            Permission::updateOrCreate(
                [
                    'name'       => $name,
                    'guard_name' => 'schooladmin',
                ],
                [
                    'permission_category_id' => $schoolManager->id,
                ]
            );
        }
    }
    private function roleManagerPermissions(): void
    {
        $roleManager = PermissionCategory::where("title", "Role Manager")->firstOrFail();

        $permissions = [
            ['name' => "appAdmin.role.delete",      'guard_name' => 'appAdmin'],
            ['name' => "appAdmin.role.update",      'guard_name' => 'appAdmin'],
            ['name' => "appAdmin.role.create",      'guard_name' => 'appAdmin'],
            ['name' => "schoolAdmin.role.assign",   'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.role.remove",   'guard_name' => 'schooladmin'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                [
                    'name'       => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ],
                [
                    'permission_category_id' => $roleManager->id,
                ]
            );
        }
    }
    private function appManagerPermissions(): void
    {
        $permissionManager = PermissionCategory::where("title", "Permission Manager")->firstOrFail();

        $permissions = [
            ['name' => "appAdmin.permission.delete",              'guard_name' => 'appAdmin'],
            ['name' => "appAdmin.permission.create",              'guard_name' => 'appAdmin'],
            ['name' => "appAdmin.permission.update",              'guard_name' => 'appAdmin'],
            ['name' => "schoolAdmin.permission.view.schoolAdmin", 'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.permission.assign",           'guard_name' => 'schooladmin'],
            ['name' => "schoolAdmin.permission.remove",           'guard_name' => 'schooladmin'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                [
                    'name'       => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ],
                [
                    'permission_category_id' => $permissionManager->id,
                ]
            );
        }
    }
    private function parentManagerPermissions(): void
    {
        $parentManager = PermissionCategory::where("title", "Parent Manager")->firstOrFail();

        $permissions = [
            "schoolAdmin.parent.create",
            "schoolAdmin.parent.update",
            "schoolAdmin.parent.view",
            "schoolAdmin.parent.delete",
            "schoolAdmin.parent.show",
        ];

        foreach ($permissions as $name) {
            Permission::updateOrCreate(
                [
                    'name'       => $name,
                    'guard_name' => 'schooladmin',
                ],
                [
                    'permission_category_id' => $parentManager->id,
                ]
            );
        }
    }




    private function marksManagerPermissions(): void
    {
        $marksManager = PermissionCategory::where("title", "Marks Manager")->firstOrFail();

        $permissions = [
            "schoolAdmin.mark.create.ca.marks",
            "schoolAdmin.mark.update.ca.marks",
            "schoolAdmin.mark.create.exam.marks",
            "schoolAdmin.mark.update.exam.marks",
            "schoolAdmin.mark.view.accessed.courses",
            "schoolAdmin.mark.view.ca.evaluation.data",
            "schoolAdmin.mark.view.exam.evaluation.data",
            "schoolAdmin.mark.view.ca.result.data",
            "schoolAdmin.mark.view.student",
        ];

        foreach ($permissions as $name) {
            Permission::updateOrCreate(
                [
                    'name'       => $name,
                    'guard_name' => 'schooladmin',
                ],
                [
                    'permission_category_id' => $marksManager->id,
                ]
            );
        }
    }
    private function anouncementCategoryPermission(): void
    {
        $marksManager = PermissionCategory::where("title", "Announcement Category Manager")->firstOrFail();

        $permissions = [
            "schoolAdmin.announcementCategory.create",
            "schoolAdmin.announcementCategory.update",
            "schoolAdmin.announcementCategory.delete",
            "schoolAdmin.announcementCategory.get",
            "schoolAdmin.announcementCategory.activate",
            "schoolAdmin.announcementCategory.deactivate",
        ];

        foreach ($permissions as $name) {
            Permission::updateOrCreate(
                [
                    'name'       => $name,
                    'guard_name' => 'schooladmin',
                ],
                [
                    'permission_category_id' => $marksManager->id,
                ]
            );
        }
    }
    private function anouncementPermission(): void
    {
        $marksManager = PermissionCategory::where("title", "Announcement Manager")->firstOrFail();

        $permissions = [
            "schoolAdmin.announcement.create",
            "schoolAdmin.announcement.update",
            "schoolAdmin.announcement.delete",
            "schoolAdmin.announcement.get",
        ];

        foreach ($permissions as $name) {
            Permission::updateOrCreate(
                [
                    'name'       => $name,
                    'guard_name' => 'schooladmin',
                ],
                [
                    'permission_category_id' => $marksManager->id,
                ]
            );
        }
    }
    private function schoolAppSettingPermission(): void
    {
        $marksManager = PermissionCategory::where("title", "School Branch Setting Manager")->firstOrFail();

        $permissions = [
            "schoolAdmin.electionSetting.update",
            "schoolAdmin.examSetting.update",
            "schoolAdmin.gradeScaleSetting.update",
            "schoolAdmin.promotionSetting.update",
            "schoolAdmin.resitSetting.update",
            "schoolAdmin.timetableSetting.update",
        ];

        foreach ($permissions as $name) {
            Permission::updateOrCreate(
                [
                    'name'       => $name,
                    'guard_name' => 'schooladmin',
                ],
                [
                    'permission_category_id' => $marksManager->id,
                ]
            );
        }
    }
    private function hallPermission(): void
    {
        $marksManager = PermissionCategory::where("title", "Hall Manager")->firstOrFail();

        $permissions = [
            "schoolAdmin.hall.update",
            "schoolAdmin.hall.create",
            "schoolAdmin.hall.delete",
            "schoolAdmin.hall.view",
            "schoolAdmin.hall.deactivated",
            "schoolAdmin.specialtyHall.available.view",
            "schoolAdmin.hall.activated",
            "schoolAdmin.specialtyHall.assign",
            "schoolAdmin.specialtyHall.remove",
        ];

        foreach ($permissions as $name) {
            Permission::updateOrCreate(
                [
                    'name'       => $name,
                    'guard_name' => 'schooladmin',
                ],
                [
                    'permission_category_id' => $marksManager->id,
                ]
            );
        }
    }
    private function resitManager(): void
    {
        $marksManager = PermissionCategory::where("title", "Resit Manager")->firstOrFail();

        $permissions = [
            // resit timetable
            "schoolAdmin.resitTimetable.create",
            "schoolAdmin.resitTimetable.update",
            "schoolAdmin.resitTimetable.delete",

            // resit exam
            "schoolAdmin.resitExam.create",
            "schoolAdmin.resitExam.update",
            "schoolAdmin.resitExam.view",
            "schoolAdmin.resitExam.delete",
            "schoolAdmin.resitExam.gradeScaleAdd",
            "schoolAdmin.resitExam.gradeScaleRemove",

            // resit candidates
            "schoolAdmin.resitCandidate.delete",
            "schoolAdmin.resitCandidate.view",

            // resit evaluation
            "schoolAdmin.resitEvaluation.addScore",
            "schoolAdmin.resitEvaluation.updateScore",
            "schoolAdmin.resitEvaluation.deleteResult",
            "schoolAdmin.resitEvaluation.viewResult",

            // resit
            "schoolAdmin.resit.delete",
            "schoolAdmin.resit.update",
            "schoolAdmin.resit.view",

            // resit payment + transactions
            "schoolAdmin.resitPayment.pay",
            "schoolAdmin.resitTransaction.delete",
            "schoolAdmin.resitTransaction.reverse",
        ];

        foreach ($permissions as $name) {
            Permission::updateOrCreate(
                [
                    'name'       => $name,
                    'guard_name' => 'schooladmin',
                ],
                [
                    'permission_category_id' => $marksManager->id,
                ]
            );
        }
    }
}
