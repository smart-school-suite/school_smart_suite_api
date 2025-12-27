<?php

namespace Database\Seeders;

use App\Models\Analytics\Academic\AcademicAnalyticEvent;
use App\Models\FeeSchedule;
use App\Models\Schooladmin;
use App\Models\Schoolbranches;
use Illuminate\Database\Seeder;
use App\Models\SettingDefination;
use App\Models\SchoolBranchSetting;
use App\Models\SettingCategory;
use App\Models\Courses;
use Illuminate\Support\Facades\Hash;
use App\Jobs\DataCreationJob\CreateStudentFeeScheduleJob;
use App\Models\ElectionType;
use App\Models\ElectionRoles;
use App\Models\Hall;
use App\Models\SchoolSemester;
use App\Models\Specialty;
use App\Models\SpecialtyHall;
use App\Models\Teacher;
use App\Models\TeacherCoursePreference;
use App\Models\TeacherSpecailtyPreference;
use App\Models\Timetable;
use App\Models\Studentbatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Faker\Factory as Faker;
use App\Events\Analytics\FinancialAnalyticsEvent;
use App\Models\Department;
use App\Models\Educationlevels;
use App\Models\Student;
use App\Models\AdditionalFeesCategory;
use Illuminate\Support\Arr;
use App\Constant\Analytics\Financial\FinancialAnalyticsEvent as EventConstant;
use App\Models\Schoolexpensescategory;
use App\Events\Analytics\EnrollmentAnalyticsEvent;
use App\Constant\Analytics\Enrollment\EnrollmentAnalyticsEvent as EnrollmentEvents;
use App\Models\StudentSource;
use App\Models\Gender;
use App\Events\Analytics\OperationalAnalyticsEvent;
use App\Constant\Analytics\Operational\OperationalAnalyticsEvent as OpEvents;
use App\Events\Analytics\ElectionAnalyticsEvent;
use App\Models\Elections;
use App\Models\Students;
use App\Models\ElectionCandidates;
use App\Models\ElectionApplication;
use App\Events\Analytics\AcademicAnalyticsEvent;
use App\Constant\Analytics\Academic\AcademicAnalyticsEvent as AcademicEvents;
use Illuminate\Support\Str;
use App\Models\Exams;
use App\Models\ExamType;
use App\Models\Semester;
use App\Models\AccessedStudent;
use App\Jobs\DataCreationJob\CreateExamCandidateJob;
use App\Models\LetterGrade;

class test extends Seeder
{
    public function run(): void
    {
        $this->academicStats();
    }

    public function academicStats()
    {
        for ($i = 0; $i < 999; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::EXAM_CANDIDATE_GPA_CALCULATED,
                version: 1,
                payload: [
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "exam_id" => Arr::random(Exams::all()->pluck('id')->toArray()),
                    "exam_type_id" => Arr::random(Examtype::all()->pluck('id')->toArray()),
                    "candidate_id" => Arr::random(AccessedStudent::all()->pluck('id')->toArray()),
                    "student_id" => Arr::random(Student::all()->pluck('id')->toArray()),
                    'school_branch_id' => Schoolbranches::first()->id,
                    "value" => rand(1.00, 4.00)
                ]
            ));
        }
        for ($i = 0; $i < 999; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::EXAM_CANDIDATE_COURSE_EVALUATED,
                version: 1,
                payload: [
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "exam_id" => Arr::random(Exams::all()->pluck('id')->toArray()),
                    "exam_type_id" => Arr::random(Examtype::all()->pluck('id')->toArray()),
                    "candidate_id" => Arr::random(AccessedStudent::all()->pluck('id')->toArray()),
                    "student_id" => Arr::random(Student::all()->pluck('id')->toArray()),
                    'school_branch_id' => Schoolbranches::first()->id,
                    "course_id" => Arr::random(Courses::all()->pluck('id')->toArray()),
                    "teacher_id" => Arr::random(Teacher::all()->pluck('id')->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 999; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::EXAM_CANDIDATE_COURSE_GRADE,
                version: 1,
                payload: [
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "exam_id" => Arr::random(Exams::all()->pluck('id')->toArray()),
                    "exam_type_id" => Arr::random(Examtype::all()->pluck('id')->toArray()),
                    "candidate_id" => Arr::random(AccessedStudent::all()->pluck('id')->toArray()),
                    "student_id" => Arr::random(Student::all()->pluck('id')->toArray()),
                    'school_branch_id' => Schoolbranches::first()->id,
                    "course_id" => Arr::random(Courses::all()->pluck('id')->toArray()),
                    "teacher_id" => Arr::random(Teacher::all()->pluck('id')->toArray()),
                    "letter_grade_id" => Arr::random(LetterGrade::all()->pluck('id')->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 999; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::EXAM_CANDIDATE_COURSE_SCORE,
                version: 1,
                payload: [
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "exam_id" => Arr::random(Exams::all()->pluck('id')->toArray()),
                    "exam_type_id" => Arr::random(Examtype::all()->pluck('id')->toArray()),
                    "candidate_id" => Arr::random(AccessedStudent::all()->pluck('id')->toArray()),
                    "student_id" => Arr::random(Student::all()->pluck('id')->toArray()),
                    'school_branch_id' => Schoolbranches::first()->id,
                    "course_id" => Arr::random(Courses::all()->pluck('id')->toArray()),
                    "teacher_id" => Arr::random(Teacher::all()->pluck('id')->toArray()),
                    "value" => rand(1, 100)
                ]
            ));
        }
        for ($i = 0; $i < 999; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::EXAM_CANDIDATE_COURSE_PASSED,
                version: 1,
                payload: [
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "exam_id" => Arr::random(Exams::all()->pluck('id')->toArray()),
                    "exam_type_id" => Arr::random(Examtype::all()->pluck('id')->toArray()),
                    "candidate_id" => Arr::random(AccessedStudent::all()->pluck('id')->toArray()),
                    "student_id" => Arr::random(Student::all()->pluck('id')->toArray()),
                    'school_branch_id' => Schoolbranches::first()->id,
                    "course_id" => Arr::random(Courses::all()->pluck('id')->toArray()),
                    "teacher_id" => Arr::random(Teacher::all()->pluck('id')->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 999; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::EXAM_CANDIDATE_COURSE_FAILED,
                version: 1,
                payload: [
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "exam_id" => Arr::random(Exams::all()->pluck('id')->toArray()),
                    "exam_type_id" => Arr::random(Examtype::all()->pluck('id')->toArray()),
                    "candidate_id" => Arr::random(AccessedStudent::all()->pluck('id')->toArray()),
                    "student_id" => Arr::random(Student::all()->pluck('id')->toArray()),
                    'school_branch_id' => Schoolbranches::first()->id,
                    "course_id" => Arr::random(Courses::all()->pluck('id')->toArray()),
                    "teacher_id" => Arr::random(Teacher::all()->pluck('id')->toArray()),
                    "value" => 1
                ]
            ));
        }

        for ($i = 0; $i < 999; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::EXAM_CANDIDATE_RESIT_INCURRED,
                version: 1,
                payload: [
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "exam_id" => Arr::random(Exams::all()->pluck('id')->toArray()),
                    "exam_type_id" => Arr::random(Examtype::all()->pluck('id')->toArray()),
                    "candidate_id" => Arr::random(AccessedStudent::all()->pluck('id')->toArray()),
                    "student_id" => Arr::random(Student::all()->pluck('id')->toArray()),
                    'school_branch_id' => Schoolbranches::first()->id,
                    "course_id" => Arr::random(Courses::all()->pluck('id')->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 999; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::EXAM_CANDIDATE_PASSED,
                version: 1,
                payload: [
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "exam_id" => Arr::random(Exams::all()->pluck('id')->toArray()),
                    "exam_type_id" => Arr::random(Examtype::all()->pluck('id')->toArray()),
                    "candidate_id" => Arr::random(AccessedStudent::all()->pluck('id')->toArray()),
                    "student_id" => Arr::random(Student::all()->pluck('id')->toArray()),
                    'school_branch_id' => Schoolbranches::first()->id,
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 999; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::EXAM_CANDIDATE_FAILED,
                version: 1,
                payload: [
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "exam_id" => Arr::random(Exams::all()->pluck('id')->toArray()),
                    "exam_type_id" => Arr::random(Examtype::all()->pluck('id')->toArray()),
                    "candidate_id" => Arr::random(AccessedStudent::all()->pluck('id')->toArray()),
                    "student_id" => Arr::random(Student::all()->pluck('id')->toArray()),
                    'school_branch_id' => Schoolbranches::first()->id,
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 999; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::EXAM_CANDIDATE_CREATED,
                version: 1,
                payload: [
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "exam_id" => Arr::random(Exams::all()->pluck('id')->toArray()),
                    "exam_type_id" => Arr::random(Examtype::all()->pluck('id')->toArray()),
                    "candidate_id" => Arr::random(AccessedStudent::all()->pluck('id')->toArray()),
                    "student_id" => Arr::random(Student::all()->pluck('id')->toArray()),
                    'school_branch_id' => Schoolbranches::first()->id,
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 999; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::EXAM_CANDIDATE_COURSE_CREATED,
                version: 1,
                payload: [
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "exam_id" => Arr::random(Exams::all()->pluck('id')->toArray()),
                    "exam_type_id" => Arr::random(Examtype::all()->pluck('id')->toArray()),
                    "candidate_id" => Arr::random(AccessedStudent::all()->pluck('id')->toArray()),
                    "student_id" => Arr::random(Student::all()->pluck('id')->toArray()),
                    'school_branch_id' => Schoolbranches::first()->id,
                    "course_id" => Arr::random(Courses::all()->pluck('id')->toArray()),
                    "teacher_id" => Arr::random(Teacher::all()->pluck('id')->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 100; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::EXAM_CREATED,
                version: 1,
                payload: [
                    "exam_id" => Arr::random(Exams::all()->pluck('id')->toArray()),
                    "exam_type_id" => Arr::random(Examtype::all()->pluck('id')->toArray()),
                    'school_branch_id' => Schoolbranches::first()->id,
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 999; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::STUDENT_EXAM_CREATED,
                version: 1,
                payload: [
                    "exam_id" => Arr::random(Exams::all()->pluck('id')->toArray()),
                    "exam_type_id" => Arr::random(Examtype::all()->pluck('id')->toArray()),
                    'school_branch_id' => Schoolbranches::first()->id,
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "student_id" => Arr::random(Student::all()->pluck('id')->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 999; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::RESIT_EXAM_CANDIDATE_EVALUATED,
                version: 1,
                payload: [
                    "exam_id" => Arr::random(Exams::all()->pluck('id')->toArray()),
                    "exam_type_id" => Arr::random(Examtype::all()->pluck('id')->toArray()),
                    'school_branch_id' => Schoolbranches::first()->id,
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "student_id" => Arr::random(Student::all()->pluck('id')->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 999; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::RESIT_EXAM_CANDIDATE_PASSED,
                version: 1,
                payload: [
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "exam_id" => Arr::random(Exams::all()->pluck('id')->toArray()),
                    "exam_type_id" => Arr::random(Examtype::all()->pluck('id')->toArray()),
                    "candidate_id" => Arr::random(AccessedStudent::all()->pluck('id')->toArray()),
                    "student_id" => Arr::random(Student::all()->pluck('id')->toArray()),
                    'school_branch_id' => Schoolbranches::first()->id,
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 999; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::RESIT_EXAM_CANDIDATE_FAILED,
                version: 1,
                payload: [
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "exam_id" => Arr::random(Exams::all()->pluck('id')->toArray()),
                    "exam_type_id" => Arr::random(Examtype::all()->pluck('id')->toArray()),
                    "candidate_id" => Arr::random(AccessedStudent::all()->pluck('id')->toArray()),
                    "student_id" => Arr::random(Student::all()->pluck('id')->toArray()),
                    'school_branch_id' => Schoolbranches::first()->id,
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 999; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::RESIT_EXAM_CREATED,
                version: 1,
                payload: [
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "exam_id" => Arr::random(Exams::all()->pluck('id')->toArray()),
                    "exam_type_id" => Arr::random(Examtype::all()->pluck('id')->toArray()),
                    'school_branch_id' => Schoolbranches::first()->id,
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 999; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::RESIT_EXAM_CANDIDATE_CREATED,
                version: 1,
                payload: [
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "exam_id" => Arr::random(Exams::all()->pluck('id')->toArray()),
                    "exam_type_id" => Arr::random(Examtype::all()->pluck('id')->toArray()),
                    'school_branch_id' => Schoolbranches::first()->id,
                    "candidate_id" => Arr::random(AccessedStudent::all()->pluck('id')->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 999; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::TEACHER_EXAM_COURSE_CREATED,
                version: 1,
                payload: [
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "exam_id" => Arr::random(Exams::all()->pluck('id')->toArray()),
                    "exam_type_id" => Arr::random(Examtype::all()->pluck('id')->toArray()),
                    'school_branch_id' => Schoolbranches::first()->id,
                    "candidate_id" => Arr::random(AccessedStudent::all()->pluck('id')->toArray()),
                    "teacher_id" => Arr::random(Teacher::all()->pluck('id')->toArray()),
                    "course_id" => Arr::random(Courses::all()->pluck('id')->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 999; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::EXAM_CANDIDATE_TOTAL_SCORE_CALCULATED,
                version: 1,
                payload: [
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "exam_id" => Arr::random(Exams::all()->pluck('id')->toArray()),
                    "exam_type_id" => Arr::random(Examtype::all()->pluck('id')->toArray()),
                    'school_branch_id' => Schoolbranches::first()->id,
                    "candidate_id" => Arr::random(AccessedStudent::all()->pluck('id')->toArray()),
                    "teacher_id" => Arr::random(Teacher::all()->pluck('id')->toArray()),
                    "course_id" => Arr::random(Courses::all()->pluck('id')->toArray()),
                    "value" => rand(30, 100)
                ]
            ));
        }
    }
    public function electionStats()
    {

        for ($i = 0; $i < 100; $i++) {
            event(new ElectionAnalyticsEvent(
                eventType: "electionType.created",
                version: 1,
                payload: [
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 125; $i++) {
            event(new ElectionAnalyticsEvent(
                eventType: "election_created",
                version: 1,
                payload: [
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,
                    "election_type_id" => Arr::random(ElectionType::all()->pluck("id")->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 200; $i++) {
            event(new ElectionAnalyticsEvent(
                eventType: "electionRole.created",
                version: 1,
                payload: [
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,
                    "election_type_id" => Arr::random(ElectionType::all()->pluck("id")->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 100; $i++) {
            event(new ElectionAnalyticsEvent(
                eventType: "candidate_registered",
                version: 1,
                payload: [
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,
                    "election_id" => Arr::random(Elections::all()->pluck('id')->toArray()),
                    "election_role_id" => Arr::random(ElectionRoles::all()->pluck('id')->toArray()),
                    "election_type_id" => Arr::random(ElectionType::all()->pluck("id")->toArray()),
                    "student_id" => Arr::random(Student::all()->pluck('id')->toArray()),
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 900; $i++) {
            event(new ElectionAnalyticsEvent(
                eventType: "vote_casted",
                version: 1,
                payload: [
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,
                    "candidate_id" => Arr::random(ElectionCandidates::all()->pluck('id')->toArray()),
                    "election_id" => Arr::random(Elections::all()->pluck('id')->toArray()),
                    "election_role_id" => Arr::random(ElectionRoles::all()->pluck('id')->toArray()),
                    "election_type_id" => Arr::random(ElectionType::all()->pluck("id")->toArray()),
                    "student_id" => Arr::random(Student::all()->pluck('id')->toArray()),
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 900; $i++) {
            event(new ElectionAnalyticsEvent(
                eventType: "vote_casted",
                version: 1,
                payload: [
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,
                    "candidate_id" => Arr::random(ElectionCandidates::all()->pluck('id')->toArray()),
                    "election_id" => Arr::random(Elections::all()->pluck('id')->toArray()),
                    "election_role_id" => Arr::random(ElectionRoles::all()->pluck('id')->toArray()),
                    "election_type_id" => Arr::random(ElectionType::all()->pluck("id")->toArray()),
                    "student_id" => Arr::random(Student::all()->pluck('id')->toArray()),
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 900; $i++) {
            event(new ElectionAnalyticsEvent(
                eventType: "vote_casted",
                version: 1,
                payload: [
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,
                    "candidate_id" => Arr::random(ElectionCandidates::all()->pluck('id')->toArray()),
                    "election_id" => Arr::random(Elections::all()->pluck('id')->toArray()),
                    "election_role_id" => Arr::random(ElectionRoles::all()->pluck('id')->toArray()),
                    "election_type_id" => Arr::random(ElectionType::all()->pluck("id")->toArray()),
                    "student_id" => Arr::random(Student::all()->pluck('id')->toArray()),
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 900; $i++) {
            event(new ElectionAnalyticsEvent(
                eventType: "vote_casted",
                version: 1,
                payload: [
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,
                    "candidate_id" => Arr::random(ElectionCandidates::all()->pluck('id')->toArray()),
                    "election_id" => Arr::random(Elections::all()->pluck('id')->toArray()),
                    "election_role_id" => Arr::random(ElectionRoles::all()->pluck('id')->toArray()),
                    "election_type_id" => Arr::random(ElectionType::all()->pluck("id")->toArray()),
                    "student_id" => Arr::random(Student::all()->pluck('id')->toArray()),
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 900; $i++) {
            event(new ElectionAnalyticsEvent(
                eventType: "vote_casted",
                version: 1,
                payload: [
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,
                    "candidate_id" => Arr::random(ElectionCandidates::all()->pluck('id')->toArray()),
                    "election_id" => Arr::random(Elections::all()->pluck('id')->toArray()),
                    "election_role_id" => Arr::random(ElectionRoles::all()->pluck('id')->toArray()),
                    "election_type_id" => Arr::random(ElectionType::all()->pluck("id")->toArray()),
                    "student_id" => Arr::random(Student::all()->pluck('id')->toArray()),
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 900; $i++) {
            event(new ElectionAnalyticsEvent(
                eventType: "vote_casted",
                version: 1,
                payload: [
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,
                    "candidate_id" => Arr::random(ElectionCandidates::all()->pluck('id')->toArray()),
                    "election_id" => Arr::random(Elections::all()->pluck('id')->toArray()),
                    "election_role_id" => Arr::random(ElectionRoles::all()->pluck('id')->toArray()),
                    "election_type_id" => Arr::random(ElectionType::all()->pluck("id")->toArray()),
                    "student_id" => Arr::random(Student::all()->pluck('id')->toArray()),
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 900; $i++) {
            event(new ElectionAnalyticsEvent(
                eventType: "vote_casted",
                version: 1,
                payload: [
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,
                    "candidate_id" => Arr::random(ElectionCandidates::all()->pluck('id')->toArray()),
                    "election_id" => Arr::random(Elections::all()->pluck('id')->toArray()),
                    "election_role_id" => Arr::random(ElectionRoles::all()->pluck('id')->toArray()),
                    "election_type_id" => Arr::random(ElectionType::all()->pluck("id")->toArray()),
                    "student_id" => Arr::random(Student::all()->pluck('id')->toArray()),
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 800; $i++) {
            event(new ElectionAnalyticsEvent(
                eventType: "election_application_submitted",
                version: 1,
                payload: [
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,
                    "election_id" => Arr::random(Elections::all()->pluck('id')->toArray()),
                    "election_role_id" => Arr::random(ElectionRoles::all()->pluck('id')->toArray()),
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "student_id" => Arr::random(Student::all()->pluck('id')->toArray()),
                    "election_type_id" => Arr::random(ElectionType::all()->pluck("id")->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 100; $i++) {
            event(new ElectionAnalyticsEvent(
                eventType: "election_application_approved",
                version: 1,
                payload: [
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,
                    "election_id" => Arr::random(Elections::all()->pluck('id')->toArray()),
                    "election_role_id" => Arr::random(ElectionRoles::all()->pluck('id')->toArray()),
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "student_id" => Arr::random(Student::all()->pluck('id')->toArray()),
                    "election_type_id" => Arr::random(ElectionType::all()->pluck("id")->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 800; $i++) {
            event(new ElectionAnalyticsEvent(
                eventType: "election_application_rejected",
                version: 1,
                payload: [
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,
                    "election_id" => Arr::random(Elections::all()->pluck('id')->toArray()),
                    "election_role_id" => Arr::random(ElectionRoles::all()->pluck('id')->toArray()),
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "student_id" => Arr::random(Student::all()->pluck('id')->toArray()),
                    "election_type_id" => Arr::random(ElectionType::all()->pluck("id")->toArray()),
                    "value" => 1
                ]
            ));
        }
    }
    public function operationalStats()
    {
        for ($i = 0; $i < 100; $i++) {
            event(new OperationalAnalyticsEvent(
                eventType: OpEvents::COURSE_CREATED,
                version: 1,
                payload: [
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                ]
            ));
        }
        for ($i = 0; $i < 200; $i++) {
            event(new OperationalAnalyticsEvent(
                eventType: OpEvents::DEPARTMENT_ACTIVATED,
                version: 1,
                payload: [
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 100; $i++) {
            event(new OperationalAnalyticsEvent(
                eventType: OpEvents::DEPARTMENT_DEACTIVATED,
                version: 1,
                payload: [
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 500; $i++) {
            event(new OperationalAnalyticsEvent(
                eventType: OpEvents::DEPARTMENT_CREATED,
                version: 1,
                payload: [
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 500; $i++) {
            event(new OperationalAnalyticsEvent(
                eventType: OpEvents::SPECIALTY_CREATED,
                version: 1,
                payload: [
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 500; $i++) {
            event(new OperationalAnalyticsEvent(
                eventType: OpEvents::SPECIALTY_ACTIVATED,
                version: 1,
                payload: [
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 500; $i++) {
            event(new OperationalAnalyticsEvent(
                eventType: OpEvents::SPECIALTY_DEACTIVATED,
                version: 1,
                payload: [
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 500; $i++) {
            event(new OperationalAnalyticsEvent(
                eventType: OpEvents::SPECIALTY_CREATED,
                version: 1,
                payload: [
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 500; $i++) {
            event(new OperationalAnalyticsEvent(
                eventType: OpEvents::STUDENT_DROPOUT,
                version: 1,
                payload: [
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "gender_id" => Arr::random(Gender::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 100; $i++) {
            event(new OperationalAnalyticsEvent(
                eventType: OpEvents::HALL_CREATED,
                version: 1,
                payload: [
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,
                    "value" => 1
                ]
            ));
        }
    }
    public function enrollmentStatSeeder()
    {
        for ($i = 0; $i < 500; $i++) {
            event(new EnrollmentAnalyticsEvent(
                eventType: EnrollmentEvents::STUDENT_ENROLLED,
                version: 1,
                payload: [
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "student_batch" => Arr::random(Studentbatch::all()->pluck('id')->toArray()),
                    "source_id" => Arr::random(StudentSource::all()->pluck('id')->toArray()),
                    "gender_id" => Arr::random(Gender::all()->pluck('id')->toArray()),
                    "value" =>  1
                ]
            ));
        }
        for ($i = 0; $i < 500; $i++) {
            event(new EnrollmentAnalyticsEvent(
                eventType: EnrollmentEvents::STUDENT_ENROLLED,
                version: 1,
                payload: [
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "student_batch" => Arr::random(Studentbatch::all()->pluck('id')->toArray()),
                    "student_source_id" => Arr::random(StudentSource::all()->pluck('id')->toArray()),
                    "gender_id" => Arr::random(Gender::all()->pluck('id')->toArray()),
                    "value" =>  1
                ]
            ));
        }
        for ($i = 0; $i < 500; $i++) {
            event(new EnrollmentAnalyticsEvent(
                eventType: EnrollmentEvents::STUDENT_ENROLLED,
                version: 1,
                payload: [
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "student_batch" => Arr::random(Studentbatch::all()->pluck('id')->toArray()),
                    "student_source_id" => Arr::random(StudentSource::all()->pluck('id')->toArray()),
                    "gender_id" => Arr::random(Gender::all()->pluck('id')->toArray()),
                    "value" =>  1
                ]
            ));
        }
        for ($i = 0; $i < 500; $i++) {
            event(new EnrollmentAnalyticsEvent(
                eventType: EnrollmentEvents::STUDENT_ENROLLED,
                version: 1,
                payload: [
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "student_batch" => Arr::random(Studentbatch::all()->pluck('id')->toArray()),
                    "student_source_id" => Arr::random(StudentSource::all()->pluck('id')->toArray()),
                    "gender_id" => Arr::random(Gender::all()->pluck('id')->toArray()),
                    "value" =>  1
                ]
            ));
        }
    }
    public function financialStatsSeeder()
    {
        for ($i = 0; $i < 500; $i++) {
            event(new FinancialAnalyticsEvent(
                eventType: 'finance.registration_fee.incurred',
                version: 1,
                payload: [
                    // ─── REQUIRED ───────────────────────
                    'amount' => rand(50000, 100000),

                    // ─── TENANCY ────────────────────────
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,

                    // ─── ACADEMIC STRUCTURE ──────────────
                    'department' => Department::first()->department_name,
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    'specialty'  => Specialty::first()->specialty_name,
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    'level'      => EducationLevels::first()->level_name,
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "category_id" => Arr::random(AdditionalFeesCategory::all()->pluck('id')->toArray()),

                    // ─── FINANCIAL STRUCTURE ─────────────
                    'fee_type' => 'tuition',

                    // ─── ACTORS (NOT DIMENSIONS) ─────────
                    'student_id' => Student::first()->id,
                    'invoice_id' => '00fc2af1-ff25-4382-84e6-1cd4393613e3',

                    //  ─── METADATA ────────────────────────
                    'currency' => 'XAF',
                    'source'   => 'billing_service',
                ]
            ));
        }

        for ($i = 0; $i < 300; $i++) {
            event(new FinancialAnalyticsEvent(
                eventType: 'finance.registration_fee.paid',
                version: 1,
                payload: [
                    // ─── REQUIRED ───────────────────────
                    'amount' => rand(50000, 100000),

                    // ─── TENANCY ────────────────────────
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,

                    // ─── ACADEMIC STRUCTURE ──────────────
                    'department' => Department::first()->department_name,
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    'specialty'  => Specialty::first()->specialty_name,
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    'level'      => EducationLevels::first()->level_name,
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),

                    // ─── FINANCIAL STRUCTURE ─────────────
                    'fee_type' => 'tuition',

                    // ─── ACTORS (NOT DIMENSIONS) ─────────
                    'student_id' => Student::first()->id,
                    'invoice_id' => '00fc2af1-ff25-4382-84e6-1cd4393613e3',

                    //  ─── METADATA ────────────────────────
                    'currency' => 'XAF',
                    'source'   => 'billing_service',
                ]
            ));
        }

        for ($i = 0; $i < 500; $i++) {
            event(new FinancialAnalyticsEvent(
                eventType: 'finance.tuition_fee.incurred',
                version: 1,
                payload: [
                    ///  ─── REQUIRED ───────────────────────
                    'amount' => rand(500000, 1000000),

                    /// ─── TENANCY ────────────────────────
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,

                    //  ─── ACADEMIC STRUCTURE ──────────────
                    'department' => Department::first()->department_name,
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    'specialty'  => Specialty::first()->specialty_name,
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    'level'      => EducationLevels::first()->level_name,
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),

                    ///  ─── FINANCIAL STRUCTURE ─────────────
                    'fee_type' => 'tuition',

                    ///  ─── ACTORS (NOT DIMENSIONS) ─────────
                    'student_id' => Student::first()->id,
                    'invoice_id' => '00fc2af1-ff25-4382-84e6-1cd4393613e3',

                    //  ─── METADATA ────────────────────────
                    'currency' => 'XAF',
                    'source'   => 'billing_service',
                ]
            ));
        }

        for ($i = 0; $i < 400; $i++) {
            event(new FinancialAnalyticsEvent(
                eventType: 'finance.tuition_fee.paid',
                version: 1,
                payload: [
                    ///  ─── REQUIRED ───────────────────────
                    'amount' => rand(500000, 1000000),

                    /// ─── TENANCY ────────────────────────
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,

                    //  ─── ACADEMIC STRUCTURE ──────────────
                    'department' => Department::first()->department_name,
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    'specialty'  => Specialty::first()->specialty_name,
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    'level'      => EducationLevels::first()->level_name,
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),

                    ///  ─── FINANCIAL STRUCTURE ─────────────
                    'fee_type' => 'tuition',

                    ///  ─── ACTORS (NOT DIMENSIONS) ─────────
                    'student_id' => Student::first()->id,
                    'invoice_id' => '00fc2af1-ff25-4382-84e6-1cd4393613e3',

                    //  ─── METADATA ────────────────────────
                    'currency' => 'XAF',
                    'source'   => 'billing_service',
                ]
            ));
        }

        for ($i = 0; $i < 300; $i++) {
            event(new FinancialAnalyticsEvent(
                eventType: EventConstant::RESIT_FEE_INCURRED,
                version: 1,
                payload: [
                    ///  ─── REQUIRED ───────────────────────
                    'amount' => rand(2500, 10000),

                    /// ─── TENANCY ────────────────────────
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,

                    //  ─── ACADEMIC STRUCTURE ──────────────
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "category_id" => Arr::random(AdditionalFeesCategory::all()->pluck('id')->toArray()),

                    'course_id' => Courses::first()->id,
                    ///  ─── FINANCIAL STRUCTURE ─────────────
                    'fee_type' => 'tuition',

                    ///  ─── ACTORS (NOT DIMENSIONS) ─────────
                    'student_id' => Student::first()->id,
                    'invoice_id' => '00fc2af1-ff25-4382-84e6-1cd4393613e3',

                    //  ─── METADATA ────────────────────────
                    'currency' => 'XAF',
                    'source'   => 'billing_service',
                ]
            ));
        }

        for ($i = 0; $i < 150; $i++) {
            event(new FinancialAnalyticsEvent(
                eventType: EventConstant::RESIT_FEE_PAID,
                version: 1,
                payload: [
                    ///  ─── REQUIRED ───────────────────────
                    'amount' => rand(2500, 10000),

                    /// ─── TENANCY ────────────────────────
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,

                    //  ─── ACADEMIC STRUCTURE ──────────────
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "category_id" => Arr::random(AdditionalFeesCategory::all()->pluck('id')->toArray()),
                    'course_id' => Arr::random(Courses::all()->pluck('id')->toArray()),
                    ///  ─── FINANCIAL STRUCTURE ─────────────
                    'fee_type' => 'tuition',

                    ///  ─── ACTORS (NOT DIMENSIONS) ─────────
                    'student_id' => Student::first()->id,
                    'invoice_id' => '00fc2af1-ff25-4382-84e6-1cd4393613e3',

                    //  ─── METADATA ────────────────────────
                    'currency' => 'XAF',
                    'source'   => 'billing_service',
                ]
            ));
        }

        for ($i = 0; $i < 450; $i++) {
            event(new FinancialAnalyticsEvent(
                eventType: EventConstant::ADDITIONAL_FEE_INCURRED,
                version: 1,
                payload: [
                    ///  ─── REQUIRED ───────────────────────
                    'amount' => rand(2500, 50000),

                    /// ─── TENANCY ────────────────────────
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,

                    //  ─── ACADEMIC STRUCTURE ──────────────
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "category_id" => Arr::random(AdditionalFeesCategory::all()->pluck('id')->toArray()),
                    ///  ─── FINANCIAL STRUCTURE ─────────────
                    'fee_type' => 'tuition',

                    ///  ─── ACTORS (NOT DIMENSIONS) ─────────
                    'student_id' => Student::first()->id,

                    //  ─── METADATA ────────────────────────
                    'currency' => 'XAF',
                    'source'   => 'billing_service',
                ]
            ));
        }

        for ($i = 0; $i < 100; $i++) {
            event(new FinancialAnalyticsEvent(
                eventType: EventConstant::ADDITIONAL_FEE_PAID,
                version: 1,
                payload: [
                    ///  ─── REQUIRED ───────────────────────
                    'amount' => rand(2500, 50000),

                    /// ─── TENANCY ────────────────────────
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,

                    //  ─── ACADEMIC STRUCTURE ──────────────
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "category_id" => Arr::random(AdditionalFeesCategory::all()->pluck('id')->toArray()),

                    ///  ─── FINANCIAL STRUCTURE ─────────────
                    'fee_type' => 'tuition',

                    ///  ─── ACTORS (NOT DIMENSIONS) ─────────
                    'student_id' => Student::first()->id,

                    //  ─── METADATA ────────────────────────
                    'currency' => 'XAF',
                    'source'   => 'billing_service',
                ]
            ));
        }

        for ($i = 0; $i < 150; $i++) {
            event(new FinancialAnalyticsEvent(
                eventType: EventConstant::EXPENSE_INCURRED,
                version: 1,
                payload: [
                    ///  ─── REQUIRED ───────────────────────
                    'amount' => rand(100000, 500000),

                    /// ─── TENANCY ────────────────────────
                    'school_id'        => Schoolbranches::first()->school_id,
                    'school_branch_id' => Schoolbranches::first()->id,

                    //  ─── ACADEMIC STRUCTURE ──────────────
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "category_id" => Arr::random(Schoolexpensescategory::all()->pluck('id')->toArray()),

                    ///  ─── FINANCIAL STRUCTURE ─────────────
                    'fee_type' => 'tuition',

                    ///  ─── ACTORS (NOT DIMENSIONS) ─────────
                    'student_id' => Student::first()->id,

                    //  ─── METADATA ────────────────────────
                    'currency' => 'XAF',
                    'source'   => 'billing_service',
                ]
            ));
        }
    }
    public function electionSettingSeeder()
    {
        $data = [
            [
                'name' => 'Additional Fee Settings',
                'key' => 'setting.category.additionalFee'
            ],
            [
                'name' => 'Exam Settings',
                'key' => 'setting.category.exam'
            ],
            [
                'name' => 'Resit Settings',
                'key' => 'setting.category.resit'
            ],
            [
                'name' => 'Time-table Settings',
                'key' => 'setting.category.timetable'
            ],
            [
                'name' => 'Student Promotion Setting',
                'key' => 'setting.category.promotion'
            ],
            [
                'name' => 'Grade Settings',
                'key' => 'setting.category.grade'
            ],
            [
                'name' => 'Election Tie Breaker Setting',
                'key' => 'setting.category.election.tie.breaker'
            ]

        ];

        foreach ($data as $setting) {
            $settingCategory = SettingCategory::where("name", $setting['name'])->first();
            $settingCategory->key = $setting['key'];
            $settingCategory->save();
        }
    }
    public function schoolHallSeeder()
    {
        $schoolBranch = SchoolBranches::first();
        if (!$schoolBranch) return;

        $lectureNames = [
            'Main Auditorium',
            'Grand Lecture Hall',
            'Amphitheatre',
            'Hall A-{num}',
            'Hall B-{num}',
            'Hall C-{num}',
            'Hall D-{num}',
            'Hall E-{num}',
            'Room {num}',
            'Lecture Room {num}',
            'Classroom {num}',
            'Seminar Room {num}',
            'Conference Room {num}',
            'Mini Auditorium {num}'
        ];

        $labNames = [
            'Computer Lab {num}',
            'Programming Lab {num}',
            'Software Lab {num}',
            'Physics Lab {num}',
            'Chemistry Lab {num}',
            'Biology Lab {num}',
            'Electronics Lab {num}',
            'Microbiology Lab',
            'Biochemistry Lab',
            'Network Lab {num}',
            'Hardware Lab {num}',
            'Robotics Lab',
            'Language Lab {num}',
            'Media Lab',
            'Design Studio {num}',
            'Science Lab {num}',
            'Research Lab {num}',
            'Advanced Lab {num}'
        ];

        $locations = [
            'Building A',
            'Building B',
            'Building C',
            'Building D',
            'Building E',
            'Main Building',
            'Science Block',
            'Engineering Block',
            'IT Block',
            'Annex Building',
            'West Wing',
            'East Wing',
            'North Block',
            'South Block'
        ];

        $halls = [];

        for ($i = 1; $i <= 100; $i++) {
            $template = $lectureNames[array_rand($lectureNames)];
            $name = str_replace('{num}', $i < 10 ? '0' . $i : $i, $template);
            if (str_contains($name, '{num}')) {
                $name = $template . ' ' . ($i < 10 ? '0' . $i : $i);
            }

            $halls[] = [
                'name'         => $name,
                'capacity'     => rand(30, 40) === 1 ? rand(200, 400) : rand(40, 150),
                'type'         => 'lecture',
                'is_exam_hall' => rand(0, 100) <= 70,
            ];
        }

        for ($i = 1; $i <= 35; $i++) {
            $template = $labNames[array_rand($labNames)];
            $name = str_replace('{num}', $i < 10 ? '0' . $i : $i, $template);
            if (str_contains($name, '{num}')) {
                $name = $template . ' ' . ($i < 10 ? '0' . $i : $i);
            }

            $halls[] = [
                'name'         => $name,
                'capacity'     => rand(20, 40),
                'type'         => 'lab',
                'is_exam_hall' => false,
            ];
        }

        shuffle($halls);

        foreach ($halls as $hall) {
            Hall::create([
                'name'             => $hall['name'],
                'capacity'         => $hall['capacity'],
                'status'           => 'available',
                'type'             => $hall['type'],
                'location'         => $locations[array_rand($locations)],
                'school_branch_id' => $schoolBranch->id,
                'is_exam_hall'     => $hall['is_exam_hall'],
            ]);
        }
    }
    public function specialtyHallSeeder()
    {
        $schoolBranch = SchoolBranches::first();
        if (!$schoolBranch) return;

        $specialties = Specialty::all();
        $halls       = Hall::all();

        if ($specialties->isEmpty() || $halls->isEmpty()) return;

        foreach ($specialties as $specialty) {
            $hallCount = rand(2, 6);
            $randomHalls = $halls->random(min($hallCount, $halls->count()));

            foreach ($randomHalls as $hall) {
                SpecialtyHall::create([
                    'specialty_id'     => $specialty->id,
                    'level_id'         => $specialty->level_id,
                    'hall_id'          => $hall->id,
                    'school_branch_id' => $schoolBranch->id,
                ]);
            }
        }
    }
    public function teacherSpecialtyPreferenceSeeder()
    {
        $schoolBranch = SchoolBranches::first();
        $teachers = Teacher::all();
        $specialties = Specialty::all();

        $specialtyIds = $specialties->pluck('id')->toArray();
        $schoolBranchId = $schoolBranch->id;

        foreach ($teachers as $teacher) {
            $numberOfSpecialties = rand(3, 5);

            $selectedSpecialtyIds = (array) array_rand(array_flip($specialtyIds), $numberOfSpecialties);

            if ($numberOfSpecialties > count($specialtyIds)) {
                $selectedSpecialtyIds = $specialtyIds;
            }

            foreach ($selectedSpecialtyIds as $specialtyId) {
                TeacherSpecailtyPreference::create([
                    'school_branch_id' => $schoolBranchId,
                    'teacher_id'       => $teacher->id,
                    'specialty_id'     => $specialtyId,
                ]);
            }
        }
    }
    public function teacherCoursePreference()
    {
        $schoolBranch = SchoolBranches::first();
        if (!$schoolBranch) return;

        $courses = Courses::where('school_branch_id', $schoolBranch->id)
            ->with(['specialty', 'level'])
            ->get();

        $teacherPreferences = TeacherSpecailtyPreference::where('school_branch_id', $schoolBranch->id)
            ->with('teacher')
            ->get();

        if ($courses->isEmpty() || $teacherPreferences->isEmpty()) return;

        foreach ($teacherPreferences as $preference) {
            $teacher = $preference->teacher;
            $preferredSpecialtyId = $preference->specialty_id;

            $matchingCourses = $courses->where('specialty_id', $preferredSpecialtyId);

            if ($matchingCourses->isEmpty()) continue;

            $preferredCount = rand(4, 10);
            $selectedCourses = $matchingCourses->random(min($preferredCount, $matchingCourses->count()));

            foreach ($selectedCourses as $course) {
                TeacherCoursePreference::create([
                    'teacher_id'       => $teacher->id,
                    'course_id'        => $course->id,
                    'school_branch_id' => $schoolBranch->id,
                ]);
            }
        }
    }
    public function timetableSeeder()
    {
        $schoolBranch = SchoolBranches::first();
        if (!$schoolBranch) {
            Log::warning('Timetable Seeder: No SchoolBranch found. Exiting seeder.');
            return;
        }

        $schoolSemesters = SchoolSemester::where('school_branch_id', $schoolBranch->id)
            ->with(['semester', 'specialty'])
            ->get();

        if ($schoolSemesters->isEmpty()) {
            Log::warning('Timetable Seeder: No SchoolSemesters found for branch ID ' . $schoolBranch->id . '. Exiting seeder.');
            return;
        }

        $teacherPrefs = TeacherCoursePreference::where('school_branch_id', $schoolBranch->id)
            ->with(['teacher', 'course'])
            ->get()
            ->groupBy('course_id');


        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        $timeSlots = [
            '07:30',
            '08:00',
            '08:30',
            '09:00',
            '09:30',
            '10:00',
            '10:30',
            '11:00',
            '11:30',
            '12:30',
            '13:00',
            '13:30',
            '14:00',
            '14:30',
            '15:00',
            '15:30',
            '16:00',
            '16:30',
            '17:00',
            '17:30',
            '18:00'
        ];

        $formatDuration = function ($minutes) {
            $hours = floor($minutes / 60);
            $mins  = $minutes % 60;
            if ($hours > 0 && $mins > 0) {
                return "{$hours}h {$mins}min";
            } elseif ($hours > 0) {
                return "{$hours}h";
            } else {
                return "{$mins}min";
            }
        };

        foreach ($schoolSemesters as $schoolSemester) {
            $specialtyId = $schoolSemester->specialty_id;
            $semesterId  = $schoolSemester->semester_id;
            $schoolSemesterId = $schoolSemester->id;
            $levelId     = $schoolSemester->specialty->level_id;
            $studentBatchId = $schoolSemester->student_batch_id;
            Log::info('Processing SchoolSemester: Specialty ID ' . $specialtyId . ', Semester ID ' . $semesterId . ', Level ID ' . $levelId);

            if (!$levelId) {
                Log::warning('Timetable Seeder: Skipping SchoolSemester ' . $schoolSemester->id . ' because Level ID is null.');
                continue;
            }

            $courses = Courses::where('school_branch_id', $schoolBranch->id)
                ->where('semester_id', $semesterId)
                ->where('specialty_id', $specialtyId)
                ->inRandomOrder()
                ->get();

            $availableHalls = SpecialtyHall::where('specialty_id', $specialtyId)
                ->where('level_id', $levelId)
                ->inRandomOrder()
                ->pluck('hall_id');


            if (!$studentBatchId || $availableHalls->isEmpty() || $courses->isEmpty()) {
                Log::warning('Timetable Seeder: Skipping Specialty/Level combination. StudentBatch ID: ' . (int) $studentBatchId .
                    ', Halls found: ' . $availableHalls->count() .
                    ', Courses found: ' . $courses->count());
                continue;
            }

            $totalEntries = rand(120, 150);
            $daysWithBreak = [];

            for ($i = 0; $i < $totalEntries; $i++) {
                $day = $days[array_rand($days)];

                if (!in_array($day, $daysWithBreak)) {
                    try {
                        Timetable::create([
                            'school_branch_id'  => $schoolBranch->id,
                            'specialty_id'      => $specialtyId,
                            'level_id'          => $levelId,
                            'course_id'         => null,
                            'teacher_id'        => null,
                            'day_of_week'       => $day,
                            'start_time'        => '12:00',
                            'end_time'          => '12:30',
                            'duration'          => '30min',
                            'semester_id'       => $schoolSemesterId,
                            'break'             => true,
                            'hall_id'           => null,
                            'student_batch_id'  => $studentBatchId,
                        ]);
                        $daysWithBreak[] = $day;
                        Log::debug('Timetable Seeder: Successfully created Break entry for ' . $day . '.');
                    } catch (\Exception $e) {
                        Log::error('Timetable Seeder: FAILED to create Break for ' . $day . '. Error: ' . $e->getMessage());
                    }
                }

                $startTime = $timeSlots[array_rand($timeSlots)];

                try {
                    $start = Carbon::createFromFormat('H:i', $startTime);
                } catch (\Exception $e) {
                    Log::error('Timetable Seeder: FAILED to create Carbon instance for start time: ' . $startTime . '. Error: ' . $e->getMessage());
                    continue;
                }

                if ($startTime >= '12:00' && $startTime < '12:30') {
                    Log::debug('Timetable Seeder: Skipping iteration ' . $i . '. Start time ' . $startTime . ' is during break.');
                    continue;
                }

                $slots   = rand(1, 4);
                $minutes = $slots * 30;
                $end     = $start->copy()->addMinutes($minutes);
                $endTime = $end->format('H:i');

                if ($endTime > '18:00') {
                    Log::debug('Timetable Seeder: Skipping iteration ' . $i . '. End time ' . $endTime . ' is after 18:00.');
                    continue;
                }

                $classStart = $start->format('H:i');
                $classEnd   = $end->format('H:i');

                if (
                    ($classStart >= '12:00' && $classStart < '12:30') ||
                    ($classEnd > '12:00' && $classEnd <= '12:30') ||
                    ($classStart < '12:00' && $classEnd > '12:30')
                ) {
                    Log::debug('Timetable Seeder: Skipping iteration ' . $i . '. Time slot ' . $classStart . '-' . $classEnd . ' conflicts with break time (12:00-12:30).');
                    continue;
                }

                $course = $courses->random();
                $prefs  = $teacherPrefs->get($course->id);

                if (!$prefs || $prefs->isEmpty()) {
                    Log::debug('Timetable Seeder: Skipping iteration ' . $i . '. No teacher preference found for Course ID ' . $course->id . '.');
                    continue;
                }

                $teacher = $prefs->random()->teacher;

                try {
                    Timetable::create([
                        'school_branch_id'  => $schoolBranch->id,
                        'specialty_id'      => $specialtyId,
                        'level_id'          => $levelId,
                        'course_id'         => $course->id,
                        'teacher_id'        => $teacher->id,
                        'day_of_week'       => $day,
                        'start_time'        => $startTime,
                        'end_time'          => $endTime,
                        'duration'          => $formatDuration($minutes),
                        'semester_id'       => $schoolSemesterId,
                        'break'             => false,
                        'hall_id'           => $availableHalls->random(),
                        'student_batch_id'  => $studentBatchId,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Timetable Seeder: FAILED to create CLASS entry for Specialty ' . $specialtyId . '/Level ' . $levelId . '. Error: ' . $e->getMessage() .
                        ' | Data: Day ' . $day . ', Start ' . $startTime . ', End ' . $endTime . ', Course ' . $course->id . ', Teacher ' . $teacher->id);
                }
            }
        }
    }
    public function electionType()
    {
        $schoolBranch = SchoolBranches::first();

        ElectionType::create([
            'election_title' => 'Student Government Election',
            'status' => 'active',
            'description' => 'This election selects the core student leadership body responsible for representing students, coordinating student activities, and acting as the main communication bridge between students and school administration.',
            'school_branch_id' => $schoolBranch->id,
        ]);

        ElectionType::create([
            'election_title' => 'Class Representative Election',
            'status' => 'active',
            'description' => 'Students vote for representatives who manage class-level concerns, communicate classroom issues, and support teachers in administering academic and behavioral policies.',
            'school_branch_id' => $schoolBranch->id,
        ]);

        ElectionType::create([
            'election_title' => 'Club Leadership Election',
            'status' => 'active',
            'description' => 'Clubs and associations elect presidents, secretaries, and coordinators to organize meetings, manage activities, and maintain structure within extracurricular groups.',
            'school_branch_id' => $schoolBranch->id,
        ]);

        ElectionType::create([
            'election_title' => 'Prefect Election',
            'status' => 'active',
            'description' => 'A formal process to elect prefects who oversee discipline, support orderliness in school operations, and assist staff in managing daily routines across the campus.',
            'school_branch_id' => $schoolBranch->id,
        ]);

        ElectionType::create([
            'election_title' => 'Sports Team Captain Election',
            'status' => 'active',
            'description' => 'Athletes select captains who lead training sessions, encourage team spirit, represent teams during competitions, and collaborate with coaches to maintain discipline and performance standards.',
            'school_branch_id' => $schoolBranch->id,
        ]);

        ElectionType::create([
            'election_title' => 'Academic Council Election',
            'status' => 'active',
            'description' => 'Students choose academic delegates responsible for voicing academic concerns, participating in curriculum improvement discussions, and contributing to decisions on academic policy.',
            'school_branch_id' => $schoolBranch->id,
        ]);

        ElectionType::create([
            'election_title' => 'Dormitory Leadership Election',
            'status' => 'active',
            'description' => 'Boarding students elect dorm heads and assistant heads who ensure orderliness, support the welfare of residents, and act as liaisons between students and dorm supervisors.',
            'school_branch_id' => $schoolBranch->id,
        ]);

        ElectionType::create([
            'election_title' => 'Cultural Committee Election',
            'status' => 'active',
            'description' => 'This election forms the team responsible for planning cultural events, promoting creative expression, and preserving traditions and diversity within the school community.',
            'school_branch_id' => $schoolBranch->id,
        ]);

        ElectionType::create([
            'election_title' => 'Health Committee Election',
            'status' => 'active',
            'description' => 'Students are elected to promote health awareness, hygiene practices, and wellness activities, working closely with school health staff to ensure a safe environment.',
            'school_branch_id' => $schoolBranch->id,
        ]);

        ElectionType::create([
            'election_title' => 'Environmental Committee Election',
            'status' => 'active',
            'description' => 'This election selects students who lead environmental conservation efforts, organize clean-up activities, promote recycling, and support sustainability initiatives on campus.',
            'school_branch_id' => $schoolBranch->id,
        ]);
    }
    public function electionRoles()
    {
        $faker = Faker::create();
        $schoolBranch = SchoolBranches::first();
        $elections = ElectionType::all();

        foreach ($elections as $election) {
            $rolesCount = rand(10, 15);

            for ($i = 0; $i < $rolesCount; $i++) {
                ElectionRoles::create([
                    'name' => ucfirst($faker->unique()->jobTitle()),
                    'description' => $faker->sentence(20),
                    'election_type_id' => $election->id,
                    'status' => $faker->randomElement(['active', 'inactive']),
                    'school_branch_id' => $schoolBranch->id,
                ]);
            }
        }
    }
    public function electionFaker()
    {
        for ($i = 0; $i < 50; $i++) {
            $faker = Faker::create();
            $appStart = Carbon::now()->addDays(rand(-30, 30));
            $appEnd = (clone $appStart)->addDays(7);
            $voteStart = (clone $appEnd)->addDay();
            $voteEnd = (clone $voteStart)->addDays(2);
            Elections::create([
                "id" =>  Str::uuid(),
                "election_type_id" => Arr::random(ElectionType::all()->pluck("id")->toArray()), // Assumes types exist
                'application_start' => $appStart,
                'application_end' => $appEnd,
                'voting_start' => $voteStart,
                'voting_end' => $voteEnd,
                'voting_status' => $faker->randomElement(['ongoing', 'ended', 'pending']),
                'application_status' => $faker->randomElement(['ongoing', 'ended', 'pending']),
                'school_year' => '2024-2025',
                'is_results_published' => false,
                'school_branch_id' => Schoolbranches::first()->id, // Assumes branches exist
                'status' => $faker->randomElement(['upcoming', 'ongoing', 'finished']),
            ]);
        }
    }
    public function electionApplication()
    {
        $faker = Faker::create();
        for ($i = 0; $i < 500; $i++) {
            ElectionApplication::create([
                "id" => Str::uuid(),
                'school_branch_id' => Schoolbranches::first()->id, // Usually matches the student/election branch
                'election_id' => Arr::random(Elections::all()->pluck('id')->toArray()), // Creates a new election if none provided
                'election_role_id' => Arr::random(ElectionRoles::all()->pluck('id')->toArray()),
                'student_id' => Arr::random(Student::all()->pluck('id')->toArray()),
                'manifesto' => $faker->paragraphs(3, true),
                'application_status' => $faker->randomElement(['pending', 'approved', 'rejected']),
                'personal_vision' => $faker->sentence(),
                'commitment_statement' => $faker->paragraph(),
            ]);
        }
    }
    public function electionCandidate()
    {
        for ($i = 0; $i < 500; $i++) {
            ElectionCandidates::create([
                "id" => Str::uuid(),
                "isActive" => true,
                "application_id" => Arr::random(ElectionApplication::all()->pluck('id')->toArray()),
                "school_branch_id" => Schoolbranches::first()->id,
                "election_id" => Arr::random(Elections::all()->pluck('id')->toArray()),
                "election_role_id" => Arr::random(ElectionRoles::all()->pluck('id')->toArray()),
                "student_id" => Arr::random(Student::all()->pluck('id')->toArray()),
            ]);
        }
    }
    public function createExam()
    {
        $faker = Faker::create();
        for ($i = 0; $i < 10; $i++) {
            $examType = ExamType::where("type", "!=", "resit")->pluck('id')->toArray();
            $startDate = Carbon::parse($faker->dateTimeBetween('now', '+6 months'));

            $endDate = (clone $startDate)->addDays(rand(1, 14));
            Exams::create([
                'id' => Str::uuid(),
                'school_branch_id' => Schoolbranches::first()->id,
                // "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                "student_batch_id" => Arr::random(Studentbatch::all()->pluck('id')->toArray()),
                "exam_type_id" => Arr::random($examType),
                "start_date" => $startDate,
                "end_date" => $endDate,
                "weighted_mark" => $faker->randomElement([30, 100]),
                "school_year" => "2026-2027",
                "semester_id" => Arr::random(Semester::all()->pluck('id')->toArray())
            ]);
        }
    }
    public function createCandidate()
    {
        $exams = Exams::all();
        foreach ($exams as $exam) {
            CreateExamCandidateJob::dispatch(
                $exam->specialty_id,
                $exam->level_id,
                $exam->student_batch_id,
                $exam->id
            );
        }
    }
}
