<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\TimetableRule;

class SpecailtyTimeTableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    //public function authorize(): bool
    //{
       // return false;
    //}
//
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'scheduleEntries' => ['required', 'array', new TimetableRule($this->scheduleEntries)],
            'scheduleEntries.*.teacher_id' => 'required|string',
            'scheduleEntries.*.course_id' => 'required|exists:courses,id',
            'scheduleEntries.*.day_of_week' => 'required|string',
            'scheduleEntries.*.start_time' => 'required|date_format:H:i',
            'scheduleEntries.*.specialty_id' => 'required|string',
            'scheduleEntries.*.level_id' => 'required|string',
            'scheduleEntries.*.semester_id' => 'required|string',
            'scheduleEntries.*.student_batch_id' => 'required|string',
            'scheduleEntries.*.end_time' => 'required|date_format:H:i|after:start_time',
        ];
    }
}
