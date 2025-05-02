<?php

namespace App\Http\Requests\AppAdmin;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateAppAdminRequest extends FormRequest
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
            'name' => 'required|string',
            'email' => 'required|email',
            'phone_number' => 'required|string'
        ];
    }
}
