<?php

namespace App\Http\Requests\SpecialtyTimetable;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Timetable\NoTimeTableClashesRule;
use App\Rules\Timetable\TeacherAvailableRule;
use App\Rules\Timetable\UpdateTimeTableSlotAvailableRule;
class UpdateTimetableByTeacherAvailability extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'scheduleEntries' => [
                'required',
                'array',
                new NoTimeTableClashesRule($this->scheduleEntries),
                new TeacherAvailableRule($this->scheduleEntries),
                new UpdateTimeTableSlotAvailableRule($this->scheduleEntries)
            ],
            'scheduleEntries.*.entry_id' => 'required|string|exists:timetable_slots,id',
            'scheduleEntries.*.teacher_id' => 'required|string|exists:teachers,id',
            'scheduleEntries.*.course_id' => 'required|exists:courses,id',
            'scheduleEntries.*.day_of_week' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'scheduleEntries.*.start_time' => 'required|date_format:H:i',
            'scheduleEntries.*.specialty_id' => 'required|string|exists:specialties,id',
            'scheduleEntries.*.level_id' => 'required|string|exists:levels,id',
            'scheduleEntries.*.semester_id' => 'required|string|exists:school_semesters,id',
            'scheduleEntries.*.student_batch_id' => 'required|string|exists:student_batches,id',
            'scheduleEntries.*.end_time' => 'required|date_format:H:i|after:start_time',
        ];
    }
}
