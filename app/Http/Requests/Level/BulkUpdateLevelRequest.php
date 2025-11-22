<?php

namespace App\Http\Requests\Level;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateLevelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Update this as needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'levels' => 'required|array',
            'levels.*.level_id' => 'required|string|exists:levels,id',
            'levels.*.name' => 'sometimes|string',
            'levels.*.level' => 'sometimes|string',
            'levels.*.program_name' => 'sometimes|string'
        ];
    }
}
