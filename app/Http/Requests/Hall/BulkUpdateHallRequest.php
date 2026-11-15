<?php

namespace App\Http\Requests\Hall;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateHallRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'halls' => 'required|array|min:1',
            'halls.*.hall_id' => 'required|string|distinct|exists:halls,id',
            'halls.*.name' => 'sometimes|nullable|string|max:150',
            'halls.*.capacity' => 'sometimes|nullable|integer|max:5000',
            'halls.*.location' => 'sometimes|nullable|string|max:200',
            'halls.*.typeIds' => 'sometimes|nullable|array',
            'halls.*.typeIds.*.type_id' => 'sometimes|nullable|string|exists:hall_types,id',
        ];
    }
}
