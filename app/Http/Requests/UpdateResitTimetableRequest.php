<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResitTimetableRequest extends FormRequest
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
            'entries' => ['required', 'array'],
            'entries.*.course_id' => 'required|exists:courses,id',
            'entries.*.entry_id' => 'required|exists:resit_examtimetable,id',
            'entries.*.start_time' => 'required|date_format:H:i',
            'entries.*.date' => 'required|date|after_or_equal:today',
            'entries.*.end_time' => 'required|date_format:H:i|after:entries.*.start_time',
        ];
    }
}
