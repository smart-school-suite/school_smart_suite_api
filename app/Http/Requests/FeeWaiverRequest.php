<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FeeWaiverRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
   //// public function authorize(): bool
    //{
       // return false;
    //}

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
         'start_date' => 'required|date',
         'end_date' => 'required|date',
         'description' => 'required|string',
         'specialty_id' => 'required|string|exists:specialty,id',
         'level_id' => 'required|string|exists:education_levels,id',
         'student_id' => 'required|string|exists:student,id'
        ];
    }
}
