<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GradesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    protected $maxGradePoints;
    public function __construct()
    {
        $this->maxGradePoints = request()->attributes->get('currentSchool')->max_gpa;
    }
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
           'grades' => 'required|array',
            'grades.*.letter_grade_id' => 'required|string',
            'grades.*.minimum_score' => 'required|numeric|min:0|max:100|regex:/^\d+(\.\d{1,2})?$/',
            'grades.*.maximum_score' => 'required|numeric|min:0|max:100|regex:/^\d+(\.\d{1,2})?$/',
            'grades.*.determinant' => 'required|string',
            'grades.*.grade_points' => "required|numeric|min:0|max:{$this->maxGradePoints}|regex:/^\d+(\.\d{1,2})?$/",
            'grades.*.exam_id' => 'required|string',
            'grades.*.grade_status' => 'required|string',
        ];
    }
}
