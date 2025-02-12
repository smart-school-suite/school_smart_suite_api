<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssiocaiteWeigtedMarkLetterGrades extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'letter_grade_id' => $this->letterGrade->id,
                'exam_id' => $this->exam->id,
                'exam_name' =>$this->exam->examtype->exam_name,
                'letter_grade' => $this->letterGrade->letter_grade,
                'maximum_score' => $this->exam->weighted_mark
        ];
    }
}
