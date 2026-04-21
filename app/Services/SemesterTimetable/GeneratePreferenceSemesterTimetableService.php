<?php

namespace App\Services\SemesterTimetable;

use App\Exceptions\AppException;
use App\Models\Courses;
use App\Models\InstructorAvailabilitySlot;
use App\Models\SchoolSemester;
use App\Models\SemesterTimetable\SemesterTimetableSlot;
use App\Models\SpecialtyHall;
use App\Models\TeacherCoursePreference;
use App\Models\TeacherSpecailtyPreference;
use App\Models\Course\JointCourseSlot;
use App\Models\Course\SemesterJoinCourseReference;
use App\Models\Course\SemesterJointCourse;
use App\Schedular\SemesterTimetable\Engine\SchedularEngine;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Models\Course\CourseSpecialty;
use App\Models\Teacher;
use Illuminate\Support\Arr;
class GeneratePreferenceSemesterTimetableService
{
    public function generateTimetable(array $requestPayload, object $currentSchool): array
    {

        $semester = $this->getSchoolSemester($requestPayload['school_semester_id']);
        $teachers = $this->getTeachers($currentSchool->id, $semester->specialty);
        $teacherIds = $teachers->pluck('teacher_id')->toArray();

        $preferred = $this->getTeacherPreferredSchedule($currentSchool->id, $semester, $teacherIds);
        if ($preferred->isEmpty()) {
            throw new AppException(
                "Teacher Prefered Teaching Slot Not Added",
                404,
                "Teacher Preferred Teaching Period Not Added",
                "Teacher Preferred Teaching for {$semester->semester->name} {$semester->specialty->specialty_name}, {$semester->specialty->level->level} please ensure that all teachers have added their preferred teaching times"
            );
        }

        $teacherCourses = $this->getTeacherCourses($currentSchool->id, $teacherIds, $semester);
        if ($teacherCourses->isEmpty()) {
            throw new AppException(
                "No Courses Assigned to teacher",
                404,
                "No Courses Assigned to teacher",
                "No Courses Assigned to this teachers found for {$semester->semester->name} {$semester->specialty->specialty_name}, {$semester->specialty->level->level}"
            );
        }

        $halls = $this->getHalls($currentSchool->id, $semester->specialty_id);

        $hallBusy = $this->getHallBusyPeriods($currentSchool->id, $halls);
        $teacherBusy = $this->getTeacherBusyPeriods($currentSchool->id, $teacherIds);

        $payload =  $this->buildBody(
            $preferred,
            $teachers,
            $teacherBusy,
            $teacherCourses,
            $halls,
            $hallBusy,
            $requestPayload,
            $this->getJointCourses($semester)
        );

        $schedular = app(SchedularEngine::class);
        $response = $schedular->run($payload);
        return [
            "timetable" => $response,
             "payload" => $payload
        ];
    }
    private function getSchoolSemester(string $id): SchoolSemester
    {
        return SchoolSemester::with(['specialty.level', 'semester'])->findOrFail($id);
    }
    private function getTeachers(string $branchId, $specialty)
    {
        $teachers = TeacherSpecailtyPreference::where('school_branch_id', $branchId)
            ->where('specialty_id', $specialty->id)
            ->with(['teacher' => fn($q) => $q->where('status', 'active')])
            ->get();

        if ($teachers->isEmpty()) {
            throw new AppException(
                "No Teachers Found",
                404,
                "No Teachers Found",
                "No teachers are assigned to {$specialty->specialty_name} {$specialty->level->name}. Please assign teachers before generating a timetable.",
            );
        }

        return $teachers;
    }
    private function getTeacherCourses(string $branchId, array $teacherIds, SchoolSemester $schoolSemester): Collection
    {
        $courseIds = CourseSpecialty::where('school_branch_id', $branchId)
            ->where('specialty_id', $schoolSemester->specialty_id)
            ->whereHas(
                'course',
                fn($q) => $q
                    ->where('school_branch_id', $branchId)
                    ->where('semester_id', $schoolSemester->semester_id)
                    ->where('status', 'active')
            )
            ->whereDoesntHave(
                'course.courseSpecialty',
                fn($q) => $q
                    ->where('school_branch_id', $branchId)
                    ->whereColumn('course_id', 'course_specialties.course_id')
                    ->where('specialty_id', '!=', $schoolSemester->specialty_id)
            )
            ->pluck('course_id')
            ->toArray();

        if (empty($courseIds)) {
            throw new AppException(
                "No Courses Found",
                404,
                "No Courses Found",
                "No active non-joint courses were found for this specialty and semester.",
            );
        }

        $teacherCourses = TeacherCoursePreference::where('school_branch_id', $branchId)
            ->whereIn('teacher_id', $teacherIds)
            ->whereIn('course_id', $courseIds)
            ->with(['course.courseSpecialty', 'teacher'])
            ->get();

        if ($teacherCourses->isEmpty()) {
            throw new AppException(
                "No Teacher-Course Assignments Found",
                404,
                "No Teacher-Course Assignments Found",
                "None of the assigned teachers have been matched to any course in this specialty and semester. Please ensure teacher-course preferences are configured.",
            );
        }

        return $teacherCourses;
    }
    private function getTeacherPreferredSchedule(string $branchId, $schoolSemester, array $teacherIds)
    {
        $preferred = InstructorAvailabilitySlot::where('school_branch_id', $branchId)
            ->where('specialty_id', $schoolSemester->specialty_id)
            ->where('school_semester_id', $schoolSemester->id)
            ->whereIn('teacher_id', $teacherIds)
            ->with('teacher')
            ->get();
            return $preferred;
    }
    private function getHalls(string $branchId, string $specialtyId)
    {
        $halls = SpecialtyHall::where('school_branch_id', $branchId)
            ->where('specialty_id', $specialtyId)
            ->with('hall.types')
            ->get();

        if ($halls->isEmpty()) {
            throw new AppException(
                "No Halls Assigned to this specialty",
                404,
                "No Halls Found For this specialty",
                "No Halls Found for specialty {$specialtyId} — please ensure that halls have been assigned to this specialty before creating timetable"
            );
        }

        return $halls;
    }
    private function getHallBusyPeriods(string $branchId, $halls)
    {
        return SemesterTimetableSlot::where('school_branch_id', $branchId)
            ->whereIn('hall_id', $halls->pluck('hall_id')->toArray())
            ->whereHas('schoolSemester', fn($q) => $q->where('end_date', '>=', now()))
            ->with('hall')
            ->get();
    }
    private function getTeacherBusyPeriods(string $branchId, array $teacherIds)
    {
        return SemesterTimetableSlot::where('school_branch_id', $branchId)
            ->whereHas('schoolSemester', fn($q) => $q->where('end_date', '>=', now()))
            ->whereIn('teacher_id', $teacherIds)
            ->with('teacher')
            ->get();
    }
    private function buildSoftConstraints(array $requestPayload): array
    {
        return array_filter([
            'course_daily_frequency'      => $requestPayload['course_daily_frequency'] ?? null,
            'course_requested_time_slots' => $requestPayload['course_requested_time_slots'] ?? null,
            'hall_requested_time_windows' => $requestPayload['hall_requested_time_windows'] ?? null,
            'requested_assignments'            => $requestPayload['requested_assignments'] ?? null,
            'teacher_daily_hours'              => $requestPayload['teacher_daily_hours'] ?? null,
            'teacher_requested_time_windows'   => $requestPayload['teacher_requested_time_windows'] ?? null,
            'teacher_weekly_hours'             => $requestPayload['teacher_weekly_hours'] ?? null,
            'schedule_periods_per_day'         => $requestPayload['schedule_periods_per_day'] ?? null,
            'schedule_free_periods_per_day'    => $requestPayload['schedule_free_periods_per_day'] ?? null,
            'requested_free_periods'           => $requestPayload['requested_free_periods'] ?? null,
        ], fn($value) => !is_null($value));
    }
    private function buildHardConstraints(array $requestPayload, ?array $jointCourses): array
    {
        return array_filter([
            'required_joint_course_periods'    => Courses::take(3)->get()->map(fn($c) => [
                'course_id' => $c->id,
                'teacher_id' => Teacher::all()->random()->id,
                'hall_id' => SpecialtyHall::all()->random()->hall_id,
                'start_time' => '08:00',
                'end_time' => '09:00',
                "day" => strtolower(Arr::random(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']))
            ])->toArray(),
            'break_period'                     => $requestPayload['break_period'] ?? null,
            'operational_period'               => $requestPayload['operational_period'] ?? null,
            'schedule_period_duration_minutes' => $requestPayload['schedule_period_duration_minutes'] ?? null,
        ], fn($value) => !is_null($value));
    }
    private function getJointCourses(SchoolSemester $schoolSemester): ?array
    {
        $branchId   = $schoolSemester->school_branch_id;
        $semesterId = $schoolSemester->id;

        $existingJointCourses = SemesterJointCourse::where('school_branch_id', $branchId)
            ->where('semester_id', $semesterId)
            ->exists();

        if (!$existingJointCourses) {
            return null;
        }

        $reference = SemesterJoinCourseReference::where('school_branch_id', $branchId)
            ->where('school_semester_id', $semesterId)
            ->first();

        if (!$reference) {
            return null;
        }

        $jointCourseSlots = JointCourseSlot::where('school_branch_id', $branchId)
            ->where('semester_joint_course_id', $reference->semester_joint_course_id)
            ->with(['course', 'teacher', 'hall'])
            ->get();

        if ($jointCourseSlots->isEmpty()) {
            throw new AppException(
                "No Joint Course Slots Found",
                404,
                "No Joint Course Slots Found",
                "Joint courses exist for this semester but no slots have been created. Please create slots for all joint courses before generating the timetable.",
            );
        }

        return $jointCourseSlots->toArray();
    }
    private function buildBody($preferred, $teachers, $teacherBusy, $teacherCourses, $halls, $hallBusy, array $requestPayload, ?array $jointCourses): array
    {
        return [
            'teacher_preferred_periods' => $preferred->map(fn($s) => [
                'start_time' => Carbon::createFromFormat('H:i:s', $s->start_time)->format('H:i'),
                'end_time' => Carbon::createFromFormat('H:i:s', $s->end_time)->format('H:i'),
                'day' => $s->day_of_week,
                'teacher_id' => $s->teacher_id,
                'teacher_name' => $s->teacher->name,
            ]),
            'teachers' => $teachers->map(fn($t) => [
                'teacher_id' => $t->teacher->id,
                'name' => $t->teacher->name,
            ]),
            'teacher_busy_periods' => $teacherBusy->map(fn($s) => [
                'start_time' => $s->start_time,
                'end_time' => $s->end_time,
                'day' => $s->day_of_week,
                'teacher_id' => $s->teacher_id,
                'teacher_name' => $s->teacher->name,
            ]),
            'teacher_courses' => $teacherCourses->map(fn($c) => [
                'course_id' => $c->course->id,
                'course_title' => $c->course->course_title,
                'course_credit' => $c->course->credit,
                'course_type' => "theoretical",
                'course_hours' => 45,
                'teacher_id' => $c->teacher->id,
                'teacher_name' => $c->teacher->name,
            ]),
            'halls' => $halls->map(fn($h) => [
                'hall_name' => $h->hall->name,
                'hall_id' => $h->hall->id,
                'hall_capacity' => $h->hall->capacity,
                'hall_type' => "lecture",
            ]),
            'hall_busy_periods' => $hallBusy->map(fn($s) => [
                'hall_id' => $s->hall->id,
                'hall_name' => $s->hall->name,
                'start_time' => $s->start_time,
                'end_time' => $s->end_time,
                'day' => $s->day_of_week,
            ]),
            'soft_constraints'     => $this->buildSoftConstraints($requestPayload),
            'hard_constraints'     => $this->buildHardConstraints($requestPayload, $jointCourses),
            ];
    }
}
