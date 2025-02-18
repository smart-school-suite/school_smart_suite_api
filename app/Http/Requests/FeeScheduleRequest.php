<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FeeScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    //public function authorize(): bool
    //{
      //  return false;
    //}

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'feeschedule' => 'required|array',
            'feeschedule.*.amount' => 'required|integer',
            'feeschedule.*.title' => 'required|string',
            'feeschedule.*.specialty_id' => 'required|string|exists:specialty,id',
            'feeschedule.*.deadline_date' => 'required|date'
        ];
    }
}
