<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfilePictureRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
   // public function authorize(): bool
    //{
        //return false;
   // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'profile_picture.required' => 'A profile picture is required.',
            'profile_picture.image' => 'The file must be an image.',
            'profile_picture.mimes' => 'Supported formats are jpeg, png, jpg, and gif.',
            'profile_picture.max' => 'The image must not be larger than 2MB.',
        ];
    }
}
