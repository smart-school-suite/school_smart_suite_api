<?php

namespace App\Http\Requests;
use App\Models\SchoolBranchApiKey;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class GradesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    protected $maxGradePoints;
    public function __construct()
    {
        $schoolBranchApiKey = request()->header('API_KEY');
        $schoolBranch = SchoolBranchApiKey::where("api_key", $schoolBranchApiKey)->with(['schoolBranch'])->first();
        $this->maxGradePoints = $schoolBranch->schoolBranch->max_gpa;
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
            'grades.*.letter_grade_id' => 'required|string|exists:letter_grade,id',
            'grades.*.minimum_score' => 'required|numeric|min:0|max:1000|regex:/^\d+(\.\d{1,2})?$/',
            'grades.*.maximum_score' => 'required|numeric|min:0|max:1000|regex:/^\d+(\.\d{1,2})?$/',
            'grades.*.max_score' => 'required|numeric|min:0|max:1000|regex:/^\d+(\.\d{1,2})?$/',
            'grades.*.determinant' => 'required|string',
            'grades.*.grade_points' => "required|numeric|min:0|max:{$this->maxGradePoints}|regex:/^\d+(\.\d{1,2})?$/",
            'grades.*.grades_category_id' => 'required|string|exists:grades_category,id',
            'grades.*.grade_status' => 'required|string',
        ];
    }
}
