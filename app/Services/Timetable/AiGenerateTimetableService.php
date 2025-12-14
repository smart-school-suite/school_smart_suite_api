<?php

namespace App\Services\Timetable;

use App\Exceptions\AppException;
use App\Models\InstructorAvailabilitySlot;
use App\Models\SchoolSemester;
use App\Models\SpecialtyHall;
use App\Models\TeacherCoursePreference;
use App\Models\TeacherSpecailtyPreference;
use App\Models\Timetable;
use App\Services\Gemini\GeminiService;
use GuzzleHttp\Client;
use Carbon\Carbon;
class AiGenerateTimetableService
{
    protected GeminiService $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }
    public function generateTimetable($data, $currentSchool)
    {

        $client = new Client();
        $schoolSemester =  SchoolSemester::with(['specialty.level', 'semester'])
            ->find($data['school_semester_id']);

        $teachers = TeacherSpecailtyPreference::where("school_branch_id", $currentSchool->id)
            ->where("specialty_id", $schoolSemester->specialty_id)
            ->with(['teacher'])
            ->get();
        if ($teachers->isEmpty()) {
            throw new AppException(
                "No Teachers Found",
                404,
                "No Teachers Found",
                "No Teachers Found for {$schoolSemester->specialty_id} {$schoolSemester->specialty->specialty_name}, {$schoolSemester->specialty->level->name} please make sure teachers has been assigned to this specialty before creating the timetable"
            );
        }
        $teacherIds = $teachers->pluck('teacher_id')->toArray();

        $teacherPreferredSchedule = InstructorAvailabilitySlot::where("school_branch_id", $currentSchool->id)
            ->where("specialty_id", $schoolSemester->specialty_id)
            ->where("school_semester_id", $schoolSemester->id)
            ->whereIn('teacher_id', $teacherIds)
            ->with(['teacher'])
            ->get();

        if ($teachers->isEmpty()) {
            throw new AppException(
                "Teacher Prefered Teaching Slot Not Added",
                404,
                "Teacher Preferred Teaching Period Not Added",
                "Teacher Preferred Teaching for {$schoolSemester->semester->name} {$schoolSemester->specialty->specialty_name}, {$schoolSemester->specialty->level->level} please ensure that all teachers have added their preferred teaching times"
            );
        }

        $teacherCourses = TeacherCoursePreference::where("school_branch_id", $currentSchool->id)
            ->whereIn("teacher_id", $teacherIds)
            ->whereHas("course", function ($query) use ($schoolSemester) {
                $query->where("semester_id", $schoolSemester->semester_id)
                    ->where("specialty_id", $schoolSemester->specialty_id);
            })->with(['course', 'teacher'])->get();

        if ($teacherCourses->isEmpty()) {
            throw new AppException(
                "No Courses Assigned to teacher",
                404,
                "No Courses Assigned to teacher",
                "No Courses Assigned to this teachers found for {$schoolSemester->semester->name} {$schoolSemester->specialty->specialty_name}, {$schoolSemester->specialty->level->level} "
            );
        }

        $halls = SpecialtyHall::where("school_branch_id", $currentSchool->id)
            ->where("specialty_id", $schoolSemester->specialty_id)
            ->with(['hall'])
            ->get();

        if ($halls->isEmpty()) {
            throw new AppException(
                "No Halls Assigned to this specialty",
                404,
                "No Halls Found For this specialty",
                "No Halls Found for {$schoolSemester->specialty->specialty_name}, {$schoolSemester->specialty->level->level} please ensure that halls have been assigned to this specialty before creating timetable"
            );
        }

        $hallBusyPeriods = Timetable::where("school_branch_id", $currentSchool->id)
            ->whereIn("hall_id", $halls->pluck('hall_id')->toArray())
            ->with(['hall'])
            ->get();

        $teacherBusyPeriods = Timetable::where("school_branch_id", $currentSchool->id)
            ->whereIn("teacher_id", $teacherIds)
            ->with(['teacher'])
            ->get();

        $promptResponse = $this->geminiService->generateStructuredJson($data['prompt']);
        $body = [
            "teacher_prefered_teaching_period" =>  $teacherPreferredSchedule->map(fn($schedule) => [
                "start_time" => Carbon::createFromFormat('H:i:s', $schedule->start_time)->format('H:i'),
                "end_time" => Carbon::createFromFormat('H:i:s', $schedule->end_time)->format('H:i'),
                "day" => $schedule->day_of_week,
                "teacher_id" => $schedule->teacher_id,
                "teacher_name" => $schedule->teacher->name
            ]),
            "teachers" => $teachers->map(fn($teacher) => [
                "teacher_id" => $teacher->teacher->id,
                "name" => $teacher->teacher->name
            ]),
            "teacher_busy_period" => $teacherBusyPeriods->map(fn($scheduleSlot) => [
                "start_time" => $scheduleSlot->start_time,
                "end_time" => $scheduleSlot->end_time,
                "day" => $scheduleSlot->day_of_week,
                "teacher_id" => $scheduleSlot->teacher_id,
                "teacher_name" => $scheduleSlot->teacher->name
            ]),
            "teacher_courses" => $teacherCourses->map(fn($course) => [
                "course_id" => $course->course->id,
                "course_title" => $course->course->course_title,
                "course_credit" => $course->course->credit,
                "course_type" => $course->course->type == "theoretical" ? "Theory" : "Practical",
                "course_hours" => 45,
                "teacher_id" => $course->teacher->id,
                "teacher_name" => $course->teacher->name
            ]),
            "halls" => $halls->map(fn($hall) => [
                "hall_name" => $hall->hall->name,
                "hall_id" => $hall->hall->id,
                "hall_capacity" => $hall->hall->capacity,
                "hall_type" => $hall->hall->type
            ]),
            "hall_busy_periods" => $hallBusyPeriods->map(fn($slot) => [
                "hall_id" => $slot->hall->id,
                "hall_name" => $slot->hall->name,
                "start_time" => $slot->start_time,
                "end_time" => $slot->end_time,
                "day" => $slot->day_of_week
            ]),
            "break_period" => collect($promptResponse["hard_constraints"])->get("break_period"),
            "operational_period" => collect($promptResponse["hard_constraints"])->get('operational_period'),
            "periods" => collect($promptResponse["hard_constraints"])->get("periods"),
            "soft_constrains" => collect($promptResponse["soft_constraints"]),
        ];
        //  $response =  $client->post("http://127.0.0.1:8080/api/v1/schedule/with-preference", [
        //   'headers' => [
        //        'Content-Type' => 'application/json',
        //         'Accept' => 'application/json',
        //     ],
        //     'json' => $body
        //  ]);

        //  $result = json_decode($response->getBody()->getContents(), true);
        return $body;
    }
}
