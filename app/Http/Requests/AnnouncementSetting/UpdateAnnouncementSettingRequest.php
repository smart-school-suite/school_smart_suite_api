<?php

namespace App\Http\Requests\AnnouncementSetting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnnouncementSettingRequest extends FormRequest
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
            'title' => 'sometimes|nullable|string|max:100',
            'description' => 'sometimes|nullable|string|max:1000'
        ];
    }
}
