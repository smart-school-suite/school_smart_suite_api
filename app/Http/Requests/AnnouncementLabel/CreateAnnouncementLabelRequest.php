<?php

namespace App\Http\Requests\AnnouncementLabel;

use Illuminate\Foundation\Http\FormRequest;

class CreateAnnouncementLabelRequest extends FormRequest
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
            'name' => 'required|string|max:50'
        ];
    }
}
