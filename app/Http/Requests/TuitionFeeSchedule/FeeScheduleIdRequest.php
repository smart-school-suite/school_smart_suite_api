<?php

namespace App\Http\Requests\TuitionFeeSchedule;

use Illuminate\Foundation\Http\FormRequest;

class FeeScheduleIdRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'feeScheduleIds' => 'required|array|min:1',
            'feeScheduleIds.*.fee_schedule_id' => 'required|string|distinct|exists:fee_schedules,id',
        ];
    }
}
