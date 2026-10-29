<?php

namespace App\Http\Requests\SchoolAdmin;

use Illuminate\Foundation\Http\FormRequest;

class UploadSchoolAdminAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'picture' => [
                'required',
                'image',
                'mimes:jpeg,png,webp',
                'max:2048',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'picture.max' => 'The picture must not be larger than 2 MB.',
            'picture.mimes' => 'Please upload a valid JPEG, PNG, or WebP image.',
        ];
    }
}
