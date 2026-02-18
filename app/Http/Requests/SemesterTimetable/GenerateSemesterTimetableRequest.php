<?php

namespace App\Http\Requests\SemesterTimetable;

use Illuminate\Foundation\Http\FormRequest;

class GenerateSemesterTimetableRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            "school_semester_id" => "required|string|exists:school_semesters,id",

            //break period validation
            "break_period" => ["nullable", "sometimes", "array"],
            "break_period.start_time" => ["required_with:break_period", "date_format:H:i"],
            "break_period.end_time" => ["required_with:break_period", "date_format:H:i", "after:break_period.start_time"],
            "break_period.no_break_exceptions" => ["sometimes", "nullable", "array", "min:1"],
            "break_period.no_break_exceptions.*" => ["required", "string", "in:monday,tuesday,wednesday,thursday,friday,saturday,sunday"],
            "break_period.day_exceptions" => ["sometimes", "nullable", "min:1"],
            "break_period.day_exceptions.*.start_time" => ["required_with:break_period.day_exceptions", "date_format:H:i"],
            "break_period.day_exceptions.*.end_time" => ["required_with:break_period.day_exceptions", "date_format:H:i", "after:break_period.day_exceptions.*.start_time"],
            "break_period.day_exceptions.*.day" => ["required_with:break_period.day_exceptions", "string", "in:monday,tuesday,wednesday,thursday,friday,saturday,sunday"],

            //operational period validation
            "operational_period" => ["required", "array"],
            "operational_period.start_time" => ["required", "date_format:H:i"],
            "operational_period.end_time" => ["required", "date_format:H:i", "after:operational_period.start_time"],
            "operational_period.day_exceptions" => ["sometimes", "nullable", "array", "min:1"],
            "operational_period.day_exceptions.*.day" => ["required_with:operational_period.day_exceptions", "string", "in:monday,tuesday,wednesday,thursday,friday,saturday,sunday"],
            "operational_period.day_exceptions.*.start_time" => ["required_with:operational_period.day_exceptions", "date_format:H:i"],
            "operational_period.day_exceptions.*.end_time" => ["required_with:operational_period.day_exceptions", "date_format:H:i", "after:operational_period.day_exceptions.*.start_time"],

            //schedule period duration validation
            "schedule_period_duration_minutes" => ["required", "array"],
            "schedule_period_duration_minutes.duration_minutes" => ["required", "integer", "min:1"],
            "schedule_period_duration_minutes.day_exceptions" => ["sometimes", "nullable", "array", "min:1"],
            "schedule_period_duration_minutes.day_exceptions.*.day" => ["required_with:schedule_period_duration_minutes.day_exceptions", "string", "in:monday,tuesday,wednesday,thursday,friday,saturday,sunday"],
            "schedule_period_duration_minutes.day_exceptions.*.duration_minutes" => ["required_with:schedule_period_duration_minutes.day_exceptions", "integer", "min:1"],

            //course max daily frequency validation
            "course_max_daily_frequency" => ["sometimes", "nullable", "array"],
            "course_max_daily_frequency.max_frequency" => ["required_with:course_max_daily_frequency", "integer", "min:1"],
            "course_max_daily_frequency.day_exceptions" => ["sometimes", "nullable", "array", "min:1"],
            "course_max_daily_frequency.day_exceptions.*.day" => ["required_with:course_max_daily_frequency.day_exceptions", "string", "in:monday,tuesday,wednesday,thursday,friday,saturday,sunday"],
            "course_max_daily_frequency.day_exceptions.*.max_frequency" => ["required_with:course_max_daily_frequency.day_exceptions", "integer", "min:1"],


            //course requested time slots validation
            "course_requested_time_slots" => ["sometimes", "nullable", "array"],
            "course_requested_time_slots.*.course_id" => ["required", "string", "exists:courses,id"],
            "course_requested_time_slots.*.slots" => ["required", "array"],
            "course_requested_time_slots.*.slots.*.day" => [
                "nullable",
                "string",
                "in:monday,tuesday,wednesday,thursday,friday,saturday,sunday"
            ],
            "course_requested_time_slots.*.slots.*.start_time" => [
                "nullable",
                "required_with:course_requested_time_slots.*.slots.*.end_time",
                "date_format:H:i"
            ],
            "course_requested_time_slots.*.slots.*.end_time" => [
                "nullable",
                "required_with:course_requested_time_slots.*.slots.*.start_time",
                "date_format:H:i",
                "after:course_requested_time_slots.*.slots.*.start_time"
            ],


            //hall requested time windows validation
            "hall_requested_time_windows" => ["sometimes", "nullable", "array"],
            "hall_requested_time_windows.*.hall_id" => ["required", "string", "exists:halls,id"],
            "hall_requested_time_windows.*.windows" => ["required", "array", "min:1"],
            "hall_requested_time_windows.*.windows.*.day" => [
                "nullable",
                "string",
                "in:monday,tuesday,wednesday,thursday,friday,saturday,sunday"
            ],
            "hall_requested_time_windows.*.windows.*.start_time" => [
                "nullable",
                "date_format:H:i",
                "required_with:hall_requested_time_windows.*.windows.*.end_time"
            ],
            "hall_requested_time_windows.*.windows.*.end_time" => [
                "nullable",
                "date_format:H:i",
                'required_with:hall_requested_time_windows.*.windows.*.start_time',
                "after:hall_requested_time_windows.*.windows.*.start_time"
            ],

            //schedule max free periods per day validation
            "schedule_max_free_periods_per_day" => ["sometimes", "nullable", "array"],
            "schedule_max_free_periods_per_day.max_free_periods" => [
                "required_with:schedule_max_free_periods_per_day",
                "integer",
                "min:0"
            ],
            "schedule_max_free_periods_per_day.day_exceptions" => [
                "nullable",
                "array",
                "min:1"
            ],
            "schedule_max_free_periods_per_day.day_exceptions.*.day" => [
                "required_with:schedule_max_free_periods_per_day.day_exceptions",
                "string",
                "in:monday,tuesday,wednesday,thursday,friday,saturday,sunday"
            ],
            "schedule_max_free_periods_per_day.day_exceptions.*.max_free_periods" => [
                "required_with:schedule_max_free_periods_per_day.day_exceptions",
                "integer",
                "min:0"
            ],

            //schedule max periods per day validation
            "schedule_max_periods_per_day" => ["sometimes", "nullable", "array"],
            "schedule_max_periods_per_day.max_periods" => [
                "required_with:schedule_max_periods_per_day",
                "integer",
                "min:0"
            ],
            "schedule_max_periods_per_day.day_exceptions" => [
                "nullable",
                "array",
                "min:1"
            ],
            "schedule_max_periods_per_day.day_exceptions.*.day" => [
                "required_with:schedule_max_periods_per_day.day_exceptions",
                "string",
                "in:monday,tuesday,wednesday,thursday,friday,saturday,sunday"
            ],
            "schedule_max_periods_per_day.day_exceptions.*.max_periods" => [
                "required_with:schedule_max_periods_per_day.day_exceptions",
                "integer",
                "min:0"
            ],

            //requested assignments validation
            "requested_assignments" => ["nullable", "array", "min:1"],
            "requested_assignments.*.course_id" => ["required", "string", "exists:courses,id"],
            "requested_assignments.*.teacher_id" => ["required", "string", "exists:teachers,id"],
            "requested_assignments.*.hall_id" => ["required", "string", "exists:halls,id"],
            "requested_assignments.*.day" => [
                "nullable",
                "string",
                "in:monday,tuesday,wednesday,thursday,friday,saturday,sunday"
            ],
            "requested_assignments.*.start_time" => [
                "nullable",
                "date_format:H:i",
                "required_with:requested_assignments.*.end_time"
            ],
            "requested_assignments.*.end_time" => [
                "nullable",
                "date_format:H:i",
                "after:requested_assignments.*.start_time",
                "required_with:requested_assignments.*.start_time"
            ],

            //requested free periods validation
            "requested_free_periods" => ["sometimes", "nullable", "array"],
            "requested_free_periods.*.day" => [
                "nullable",
                "string",
                "in:monday,tuesday,wednesday,thursday,friday,saturday,sunday"
            ],
            "requested_free_periods.*.start_time" => [
                "nullable",
                "date_format:H:i",
                "required_with:requested_free_periods.*.end_time"
            ],
            "requested_free_periods.*.end_time" => [
                "nullable",
                "date_format:H:i",
                "after:requested_free_periods.*.start_time",
                "required_with:requested_free_periods.*.start_time"
            ],

            //teacher max daily hours validation
            "teacher_max_daily_hours" => ["nullable", "array"],
            "teacher_max_daily_hours.max_hours" => ["required_with:teacher_max_daily_hours", "integer", "min:1"],
            "teacher_max_daily_hours.teacher_exceptions" => ["sometimes", "nullable", "array", "min:1"],
            "teacher_max_daily_hours.teacher_exceptions.*.teacher_id" => ["required", "string", "exists:teachers,id"],
            "teacher_max_daily_hours.teacher_exceptions.*.max_hours" => ["required", "integer", "min:1"],

            //teacher max weekly hours validation
            "teacher_max_weekly_hours" => ["nullable", "array"],
            "teacher_max_weekly_hours.max_hours" => ["required_with:teacher_max_weekly_hours", "integer", "min:1"],
            "teacher_max_weekly_hours.teacher_exceptions" => ["sometimes", "nullable", "array", "min:1"],
            "teacher_max_weekly_hours.teacher_exceptions.*.teacher_id" => ["required", "string", "exists:teachers,id"],
            "teacher_max_weekly_hours.teacher_exceptions.*.max_hours" => ["required", "integer", "min:1"],

            //teacher requested time windows validation
            "teacher_requested_time_windows" => ["sometimes", "nullable", "array"],
            "teacher_requested_time_windows.*.teacher_id" => ["required", "string", "exists:teachers,id"],
            "teacher_requested_time_windows.*.time_windows" => ["required", "array", "min:1"],
            "teacher_requested_time_windows.*.time_windows.*.day" => [
                "nullable",
                "string",
                "in:monday,tuesday,wednesday,thursday,friday,saturday,sunday"
            ],
            "teacher_requested_time_windows.*.time_windows.*.start_time" => [
                "nullable",
                "date_format:H:i",
                "required_with:teacher_requested_time_windows.*.time_windows.*.end_time"
            ],
            "teacher_requested_time_windows.*.time_windows.*.end_time" => [
                "nullable",
                "date_format:H:i",
                "after:teacher_requested_time_windows.*.time_windows.*.start_time",
                "required_with:teacher_requested_time_windows.*.time_windows.*.start_time"
            ]
        ];
    }
}
