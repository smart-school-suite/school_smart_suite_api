<?php

namespace App\Http\Requests\AppAdmin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppAdminRequest extends FormRequest
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
            'name' => 'seomtimes|nullable|string',
            'email' => 'seomtimes|nullable|email',
            'password' => 'seomtimes|nullable|string|min:8|confirmed',
            'phone_number' => 'seomtimes|nullable|string'
        ];
    }
}
