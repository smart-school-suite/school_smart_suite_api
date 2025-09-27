<?php

namespace App\Http\Requests\TuitionFeeSchedule;

use Illuminate\Foundation\Http\FormRequest;

class AutoCreateFeePaymentScheduleRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
           'fee_schedule_id' => 'required|string|exists:fee_schedules,id',
           'installments' => 'required|integer|max:5|min:1',
           'percentage' => 'required|numeric|min:0|max:100|decimal:0,2',
        ];
    }
}
