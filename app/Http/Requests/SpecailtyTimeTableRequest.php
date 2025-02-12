<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'specialty_timetable' => 'required|array',
            'specialty_timetable.*.teacher_id' => 'required|string',
            'specialty_timetable.*.course_id' => 'required|exists:courses,id',
            'specialty_timetable.*.day_of_week' => 'required|string',
            'specialty_timetable.*.start_time' => 'required|date_format:H:i:s',
            'specialty_timetable.*.specialty_id' => 'required|string',
            'specialty_timetable.*.level_id' => 'required|string',
            'specialty_timetable.*.semester_id' => 'required|string',
            'specialty_timetable.*.end_time' => 'required|date_format:H:i:s|after:start_time',
        ];
    }
}
