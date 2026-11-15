<?php

namespace App\Http\Requests\TuitionFee;

use Illuminate\Foundation\Http\FormRequest;

class TuitionFeeIdRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tuitionFeeIds' => 'required|array|min:1',
            'tuitionFeeIds.*.tuition_fee_id' => 'required|string|distinct|exists:tuition_fees,id',
        ];
    }
}
