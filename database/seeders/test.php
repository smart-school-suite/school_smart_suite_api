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
use App\Jobs\JointCourse\CreateJointCourseSemesterJob;
use App\Models\AcademicYear\SchoolAcademicYear;
use App\Models\Exams;
use App\Models\ExamType;
use App\Models\AccessedStudent;
use App\Models\Course\CourseSpecialty;
use App\Models\Course\CourseType;
use App\Models\Courses;
use App\Models\Hall;
use App\Models\HallType;
use App\Models\LetterGrade;
use App\Models\Teacher;
use App\Models\Plan;
use App\Models\SchoolSemester;
use App\Models\Semester;
use App\Models\TeacherCoursePreference;
use Faker\Factory as Faker;
use Carbon\Carbon;

class test extends Seeder
{
    public function run(): void
    {

        $currentSchool = Schoolbranches::find("50207b5e-65fb-46ca-b507-963931071777");
        $schoolSemesters = SchoolSemester::where("school_branch_id", $currentSchool->id)
            ->get();
        foreach ($schoolSemesters as $schoolSemester) {
            CreateJointCourseSemesterJob::dispatch(
                $schoolSemester->id,
                $currentSchool
            );
        }
        // $faker = Faker::create();

        // for ($i = 0; $i < 50; $i++) {

        //     $courseCode = strtoupper($faker->bothify('??-###')); // e.g., CS-101
        //     $courseTitle = $faker->words(3, true);
        //     $credit = $faker->randomElement([2, 3, 4, 6]);
        //     $courseDescription = $faker->sentence();
        //     $timestamp = Carbon::now();

        //     // 3. Pick a random semester count (assuming 1 or 2)
        //     $semesterCount = $faker->randomElement([1, 2]);

        //     $course = Courses::create([
        //         'course_code'      => $courseCode,
        //         'course_title'     => ucwords($courseTitle),
        //         'school_branch_id' => "50207b5e-65fb-46ca-b507-963931071777",
        //         'credit'           => $credit,
        //         'description'      => $courseDescription,
        //         'semester_id' => Semester::where("count", $semesterCount)->first()->id ?? 1,
        //         'created_at'       => $timestamp,
        //         'updated_at'       => $timestamp,
        //     ]);

        //     TeacherCoursePreference::create([
        //         'teacher_id' => Teacher::all()->random()->id,
        //         'course_id' => $course->id,
        //         'school_branch_id' => "50207b5e-65fb-46ca-b507-963931071777",
        //         'created_at' => $timestamp,
        //         'updated_at' => $timestamp,
        //     ]);

        //     for ($j = 0; $j < 30; $j++) {
        //         $randomSpecialtyId = Specialty::where("school_branch_id", "50207b5e-65fb-46ca-b507-963931071777")->get()->random()->id;
        //         $existingCourseSpecialty = CourseSpecialty::where('course_id', $course->id)
        //             ->where("specialty_id", $randomSpecialtyId)
        //             ->first();
        //         if (!$existingCourseSpecialty) {
        //             CourseSpecialty::create([
        //                 'course_id' => $course->id,
        //                 'specialty_id' => $randomSpecialtyId,
        //                 'school_branch_id' => "50207b5e-65fb-46ca-b507-963931071777",
        //                 'created_at' => $timestamp,
        //                 'updated_at' => $timestamp,
        //             ]);
        //         }
        //     }
        // }
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
