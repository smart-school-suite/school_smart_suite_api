<?php

namespace App\Http\Requests\Hall;

use Illuminate\Foundation\Http\FormRequest;

class BulkRemoveSpecialtyHallRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'specialtyIds' => 'required|array|min:1',
            'specialtyIds.*.specialty_id' => 'required|string|distinct|exists:specialties,id',
        ];
    }
}
