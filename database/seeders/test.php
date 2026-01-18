<?php

namespace Database\Seeders;

use App\Models\Schoolbranches;
use Illuminate\Database\Seeder;
use App\Models\Specialty;
use App\Models\Department;
use App\Models\Educationlevels;
use Illuminate\Support\Arr;
use App\Events\Analytics\AcademicAnalyticsEvent;
use App\Constant\Analytics\Academic\AcademicAnalyticsEvent as AcademicEvents;
use App\Models\Exams;
use App\Models\ExamType;
use App\Models\AccessedStudent;
use App\Models\Course\CourseType;
use App\Models\Courses;
use App\Models\Hall;
use App\Models\HallType;
use App\Models\LetterGrade;
use App\Models\Teacher;
use App\Models\Plan;

class test extends Seeder
{
    public function run(): void
    {
        $hallTypes = CourseType::pluck('id')->toArray();
        $halls = Courses::all();

        foreach ($halls as $hall) {

            $count = rand(1, 2);
            $selectedTypeIds = Arr::random($hallTypes, $count);

            $syncData = [];

            foreach ((array) $selectedTypeIds as $typeId) {
                $syncData[$typeId] = [
                    'school_branch_id' => $hall->school_branch_id,
                ];
            }

            $hall->types()->sync($syncData);
        }
    }


    public function academicStats()
    {
        for ($i = 0; $i < 500; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::EXAM_CANDIDATE_EVALUATED,
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

        for ($i = 0; $i < 500; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::EXAM_CANDIDATE_GPA_CALCULATED,
                version: 1,
                payload: [
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "exam_id" => Arr::random(Exams::all()->pluck('id')->toArray()),
                    "exam_type_id" => Arr::random(Examtype::all()->pluck('id')->toArray()),
                    'school_branch_id' => Schoolbranches::first()->id,
                    "candidate_id" => Arr::random(AccessedStudent::all()->pluck('id')->toArray()),
                    "value" => mt_rand(150, 400) / 100
                ]
            ));
        }

        for ($i = 0; $i < 350; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::EXAM_CANDIDATE_PASSED,
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

        for ($i = 0; $i < 150; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::EXAM_CANDIDATE_FAILED,
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


        for ($i = 0; $i < 500; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::EXAM_CANDIDATE_COURSE_GRADE,
                version: 1,
                payload: [
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "exam_id" => Arr::random(Exams::all()->pluck('id')->toArray()),
                    "exam_type_id" => Arr::random(Examtype::all()->pluck('id')->toArray()),
                    'school_branch_id' => Schoolbranches::first()->id,
                    "candidate_id" => Arr::random(AccessedStudent::all()->pluck('id')->toArray()),
                    "letter_grade_id" => Arr::random(LetterGrade::all()->pluck('id')->toArray()),
                    'teacher_id' => Arr::random(Teacher::all()->pluck("id")->toArray()),
                    "value" => 1
                ]
            ));
        }

        for ($i = 0; $i < 500; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::EXAM_CANDIDATE_COURSE_GRADE,
                version: 1,
                payload: [
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "exam_id" => Arr::random(Exams::all()->pluck('id')->toArray()),
                    "exam_type_id" => Arr::random(Examtype::all()->pluck('id')->toArray()),
                    'school_branch_id' => Schoolbranches::first()->id,
                    "candidate_id" => Arr::random(AccessedStudent::all()->pluck('id')->toArray()),
                    "letter_grade_id" => Arr::random(LetterGrade::all()->pluck('id')->toArray()),
                    'teacher_id' => Arr::random(Teacher::all()->pluck("id")->toArray()),
                    "value" => 1
                ]
            ));
        }

        for ($i = 0; $i < 500; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::EXAM_CANDIDATE_COURSE_GRADE,
                version: 1,
                payload: [
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "exam_id" => Arr::random(Exams::all()->pluck('id')->toArray()),
                    "exam_type_id" => Arr::random(Examtype::all()->pluck('id')->toArray()),
                    'school_branch_id' => Schoolbranches::first()->id,
                    "candidate_id" => Arr::random(AccessedStudent::all()->pluck('id')->toArray()),
                    "letter_grade_id" => Arr::random(LetterGrade::all()->pluck('id')->toArray()),
                    'teacher_id' => Arr::random(Teacher::all()->pluck("id")->toArray()),
                    "value" => 1
                ]
            ));
        }

        for ($i = 0; $i < 500; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::EXAM_CANDIDATE_RESIT_INCURRED,
                version: 1,
                payload: [
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "exam_id" => Arr::random(Exams::all()->pluck('id')->toArray()),
                    "exam_type_id" => Arr::random(Examtype::all()->pluck('id')->toArray()),
                    'school_branch_id' => Schoolbranches::first()->id,
                    "candidate_id" => Arr::random(AccessedStudent::all()->pluck('id')->toArray()),
                    "letter_grade_id" => Arr::random(LetterGrade::all()->pluck('id')->toArray()),
                    'teacher_id' => Arr::random(Teacher::all()->pluck("id")->toArray()),
                    "value" => 1
                ]
            ));
        }
        for ($i = 0; $i < 100; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::EXAM_CREATED,
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

        for ($i = 0; $i < 50; $i++) {
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

        for ($i = 0; $i < 50; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::RESIT_EXAM_CANDIDATE_EVALUATED,
                version: 1,
                payload: [
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "exam_id" => Arr::random(Exams::all()->pluck('id')->toArray()),
                    "exam_type_id" => Arr::random(Examtype::where("type",  "resit")->pluck('id')->toArray()),
                    'school_branch_id' => Schoolbranches::first()->id,
                    "value" => 1
                ]
            ));
        }

        for ($i = 0; $i < 33; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::RESIT_EXAM_CANDIDATE_FAILED,
                version: 1,
                payload: [
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "exam_id" => Arr::random(Exams::all()->pluck('id')->toArray()),
                    "exam_type_id" => Arr::random(Examtype::where("type",  "resit")->pluck('id')->toArray()),
                    'school_branch_id' => Schoolbranches::first()->id,
                    "value" => 1
                ]
            ));
        }


        for ($i = 0; $i < 17; $i++) {
            event(new AcademicAnalyticsEvent(
                eventType: AcademicEvents::RESIT_EXAM_CANDIDATE_PASSED,
                version: 1,
                payload: [
                    "department_id" => Arr::random(Department::all()->pluck('id')->toArray()),
                    "specialty_id" => Arr::random(Specialty::all()->pluck('id')->toArray()),
                    "level_id" => Arr::random(Educationlevels::all()->pluck('id')->toArray()),
                    "exam_id" => Arr::random(Exams::all()->pluck('id')->toArray()),
                    "exam_type_id" => Arr::random(Examtype::where("type",  "resit")->pluck('id')->toArray()),
                    'school_branch_id' => Schoolbranches::first()->id,
                    "value" => 1
                ]
            ));
        }
    }
}
