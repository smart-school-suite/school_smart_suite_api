<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamCandidateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "grades_submitted" => $this->grades_submitted,
            "student_accessed" => $this->student_accessed,
            "student_name" => $this->student->name,
            "student_id" => $this->student->id,
            "level" => $this->student->level->level ?? null,
            "level_name" => $this->student->level->name ?? null,
            "specialty_name" => $this->student->specialty->specialty_name,
            "specialty_id" => $this->student->specialty->id,
            "level_id" => $this->studnet->level->id ?? null,
            "exam_name" => $this->exam->examtype->exam_name,
            "exam_type" => $this->exam->examtype->type,
            "exam_id" => $this->exam->id
        ];
    }
}
