<?php

namespace App\Http\Requests\ExamTimetable;

use App\Constant\Constraint\ExamTimetable\Assignment\RequestedAssignment;
use App\Constant\Constraint\ExamTimetable\Course\CourseTimeRequest;
use App\Constant\Constraint\ExamTimetable\Hall\HallRequestedSlot;
use App\Constant\Constraint\ExamTimetable\Invigilator\InvigilatorRequestedSlot;
use App\Constant\Constraint\ExamTimetable\Schedule\OperationalPeriod;
use App\Constant\Constraint\ExamTimetable\Schedule\SessionDuration;
use App\Constant\Constraint\ExamTimetable\Student\StudentDailyLoadRange;
use Illuminate\Foundation\Http\FormRequest;

class GenerateExamTimetableRequest extends FormRequest
{
    public function rules(): array
    {
        $op = OperationalPeriod::KEY;
        $sd = SessionDuration::KEY;
        $ra = RequestedAssignment::KEY;
        $cr = CourseTimeRequest::KEY;
        $hr = HallRequestedSlot::KEY;
        $ir = InvigilatorRequestedSlot::KEY;
        $dl = StudentDailyLoadRange::KEY;

        return [
            "exam_id" => ["required", "string", "exists:exams,id"],
            "version_id" => ["sometimes", "nullable", "string", "exists:exam_timetable_versions,id"],

            // One invigilator per this many candidates in a hall (at least one per hall).
            "candidates_per_invigilator" => ["sometimes", "nullable", "integer", "min:1"],

            // Operational period (hard)
            $op => ["required", "array"],
            "$op.start_time" => ["required", "date_format:H:i"],
            "$op.end_time" => ["required", "date_format:H:i", "after:$op.start_time"],
            "$op.date_exceptions" => ["sometimes", "nullable", "array"],
            "$op.date_exceptions.*.date" => ["required", "date_format:Y-m-d"],
            "$op.date_exceptions.*.start_time" => ["required", "date_format:H:i"],
            "$op.date_exceptions.*.end_time" => ["required", "date_format:H:i", "after:$op.date_exceptions.*.start_time"],
            "$op.date_exclusions" => ["sometimes", "nullable", "array"],
            "$op.date_exclusions.*" => ["required", "date_format:Y-m-d"],

            // Session duration (hard)
            $sd => ["required", "array"],
            "$sd.duration" => ["required", "integer", "min:1", "max:1440"],
            "$sd.course_exceptions" => ["sometimes", "nullable", "array"],
            "$sd.course_exceptions.*.course_id" => ["required", "string", "exists:courses,id"],
            "$sd.course_exceptions.*.duration" => ["required", "integer", "min:1", "max:1440"],

            // Requested assignment (soft)
            $ra => ["sometimes", "nullable", "array"],
            "$ra.*.course_id" => ["required", "string", "exists:courses,id"],
            "$ra.*.date" => ["required", "date_format:Y-m-d"],
            "$ra.*.start_time" => ["required", "date_format:H:i"],
            "$ra.*.end_time" => ["required", "date_format:H:i", "after:$ra.*.start_time"],
            "$ra.*.hall_ids" => ["sometimes", "nullable", "array"],
            "$ra.*.hall_ids.*" => ["required", "string", "exists:halls,id"],
            "$ra.*.invigilator_ids" => ["sometimes", "nullable", "array"],
            "$ra.*.invigilator_ids.*" => ["required", "string", "exists:exam_invigilators,id"],

            // Course requested slot (soft)
            $cr => ["sometimes", "nullable", "array"],
            "$cr.*.course_id" => ["required", "string", "exists:courses,id"],
            "$cr.*.date" => ["required", "date_format:Y-m-d"],
            "$cr.*.start_time" => ["required", "date_format:H:i"],
            "$cr.*.end_time" => ["required", "date_format:H:i", "after:$cr.*.start_time"],

            // Hall requested slot (soft)
            $hr => ["sometimes", "nullable", "array"],
            "$hr.*.hall_id" => ["required", "string", "exists:halls,id"],
            "$hr.*.date" => ["required", "date_format:Y-m-d"],
            "$hr.*.start_time" => ["required", "date_format:H:i"],
            "$hr.*.end_time" => ["required", "date_format:H:i", "after:$hr.*.start_time"],

            // Invigilator requested slot (soft)
            $ir => ["sometimes", "nullable", "array"],
            "$ir.*.invigilator_id" => ["required", "string", "exists:exam_invigilators,id"],
            "$ir.*.date" => ["required", "date_format:Y-m-d"],
            "$ir.*.start_time" => ["required", "date_format:H:i"],
            "$ir.*.end_time" => ["required", "date_format:H:i", "after:$ir.*.start_time"],

            // Student daily load range (soft)
            $dl => ["sometimes", "nullable", "array"],
            "$dl.max_sessions" => ["required_with:$dl", "integer", "min:1", "max:10"],
            "$dl.min_sessions" => ["sometimes", "nullable", "integer", "min:0", "lte:$dl.max_sessions"],
            "$dl.date_exceptions" => ["sometimes", "nullable", "array"],
            "$dl.date_exceptions.*.date" => ["required", "date_format:Y-m-d"],
            "$dl.date_exceptions.*.max_sessions" => ["required", "integer", "min:1", "max:10"],
            "$dl.date_exceptions.*.min_sessions" => ["sometimes", "nullable", "integer", "min:0", "lte:$dl.date_exceptions.*.max_sessions"],
        ];
    }
}
