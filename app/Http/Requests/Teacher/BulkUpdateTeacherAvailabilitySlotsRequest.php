<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateTeacherAvailabilitySlotsRequest extends FormRequest
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
            'instructor_availability' => 'required|array',
            'instructor_availability.*.teacher_id' => 'sometimes|string|exists:teacher,id',
            'instructor_availability.*.day_of_week' => 'sometimes|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'instructor_availability.*.start_time' => 'sometimes|date_format:H:i',
            'instructor_availability.*.end_time' => 'sometimes|date_format:H:i|after:start_time',
        ];
    }
}
