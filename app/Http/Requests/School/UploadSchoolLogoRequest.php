<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

class UploadSchoolLogoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
       public function rules(): array
    {
        return [
            'school_logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'school_logo.required' => 'A profile picture is required.',
            'school_logo.image' => 'The file must be an image.',
            'school_logo.mimes' => 'Supported formats are jpeg, png, jpg, and gif.',
            'school_logo.max' => 'The image must not be larger than 2MB.',
        ];
    }
}
