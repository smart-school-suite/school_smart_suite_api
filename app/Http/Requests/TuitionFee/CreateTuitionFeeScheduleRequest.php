<?php

namespace App\Http\Requests\TuitionFee;

use Illuminate\Foundation\Http\FormRequest;

class CreateTuitionFeeScheduleRequest extends FormRequest
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
            'feeschedule' => 'required|array',
            'feeschedule.*.amount' => 'required|integer',
            'feeschedule.*.title' => 'required|string',
            'feeschedule.*.specialty_id' => 'required|string|exists:specialties,id',
            'feeschedule.*.deadline_date' => 'required|date'
        ];
    }
}
