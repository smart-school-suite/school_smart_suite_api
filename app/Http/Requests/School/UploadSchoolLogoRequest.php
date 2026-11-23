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
            'school_logo' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'school_logo.required' => 'A school logo is required.',
            'school_logo.image' => 'The file must be an image.',
            'school_logo.mimes' => 'Supported formats are JPEG, PNG and WebP.',
            'school_logo.max' => 'The image must not be larger than 2MB.',
        ];
    }
}
