<?php

namespace App\Http\Requests\SpecialtyTimetable;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Timetable\NoTimeTableClashesRule;
use App\Rules\Timetable\UpdateTimeTableSlotAvailableRule;
class UpdateTimetableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'scheduleEntries' => [
                'required',
                'array',
                new NoTimeTableClashesRule($this->scheduleEntries),
                new UpdateTimeTableSlotAvailableRule($this->scheduleEntries)
            ],
            'scheduleEntries.*.entry_id' => 'required|string|exists:timetables,id',
            'scheduleEntries.*.teacher_id' => 'required|string|exists:teacher,id',
            'scheduleEntries.*.course_id' => 'required|exists:courses,id',
            'scheduleEntries.*.day_of_week' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'scheduleEntries.*.start_time' => 'required|date_format:H:i',
            'scheduleEntries.*.specialty_id' => 'required|string|exists:specialty,id',
            'scheduleEntries.*.level_id' => 'required|string|exists:education_levels,id',
            'scheduleEntries.*.semester_id' => 'required|string|exists:school_semesters,id',
            'scheduleEntries.*.student_batch_id' => 'required|string|exists:student_batch,id',
            'scheduleEntries.*.end_time' => 'required|date_format:H:i|after:start_time',
        ];
    }
}
