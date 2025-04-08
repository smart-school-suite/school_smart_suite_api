<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccessedStudentResitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student->id,
            'exam_id' => $this->exam->id,
            'student_name' => $this->student->name,
            'grades_submitted' => $this->grades_submitted,
            'exam_name' => $this->exam->examType->exam_name,
            'specialty_name' => $this->exam->specialty->specialty_name,
            'level_name' => $this->exam->level->name,
            'level' => $this->exam->level->level,
            'student_accessed' => $this->student_accessed,
            'weighted_mark' => $this->exam->weighted_mark
        ];
    }
}
