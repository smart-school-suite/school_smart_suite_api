<?php

namespace App\Http\Requests\Country;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCountryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'country' => 'sometimes|nullable|string|unique',
            'code' => 'sometimes|nullable|string',
            'currency' => 'sometimes|nullable|string',
            'official_language' => 'sometimes|nullable|string'
        ];
    }
}
