<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InstructorAvialabiltyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
  //  public function authorize(): bool
   // {
   //     return false;
   // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'instructor_availability' => 'required|array',
                'instructor_availability.*.teacher_id' => 'required|string',
                'instructor_availability.*.day_of_week' => 'required|string',
                'instructor_availability.*.start_time' => 'required|date_format:H:i',
                'instructor_availability.*.end_time' => 'required|date_format:H:i|after:start_time',
                'instructor_availability.*.semester_id' => 'required|integer',
        ];
    }
}
