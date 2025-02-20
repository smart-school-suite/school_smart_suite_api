<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddtionalFeesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    //public function authorize(): bool
    //{
        //return false;
   // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
           'title' => 'required|string',
           'reason' => 'required|string',
           'amount' => 'required|integer',
           'specialty_id' => 'required|string|exists:specialty,id',
           'level_id' => 'required|string|exists:education_levels,id',
           'student_id' => 'required|string|exists:student,id'
        ];
    }
}
