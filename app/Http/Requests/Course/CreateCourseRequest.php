<?php

namespace App\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\RichTextMax;
use App\Http\Requests\Concerns\SanitizesRichText;

class CreateCourseRequest extends FormRequest
{
    use SanitizesRichText;

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
            'course_code' => 'required|string',
            'course_title' => 'required|string',
            'specialty_id' => 'required|string|exists:specialties,id',
            'credit' => 'required|integer|max:10',
            'semester_id' => 'required|string|exists:semesters,id',
            'description' => ['nullable', 'string', new RichTextMax(500)],
            'typeIds' => "required|array",
            "typeIds.*.type_id" => "required|string|exists:course_types,id"
        ];
    }
}
