<?php

namespace App\Http\Requests\AnnouncementCategory;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\RichTextMax;
use App\Http\Requests\Concerns\SanitizesRichText;

class CreateAnnouncementCategoryRequest extends FormRequest
{
    use SanitizesRichText;

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
            'name' => 'required|string|max:100',
            'description' => ['required', 'string', new RichTextMax(1000)]
        ];
    }
}
