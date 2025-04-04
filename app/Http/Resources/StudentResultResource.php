<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResultResource extends JsonResource
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
            'gpa' => $this->gpa,
            'student_name' => $this->student->name,
            'school_branch_id' => $this->school_branch_id,
            'specialty_id' => $this->specialty->specialty_name,
            'level_name' => $this->level->level_name,
            'level' => $this->level->level,
            'exam_name' => $this->exam->examtype->exam_name,
            'batch_title' => $this->studentBatch->name
        ];
    }
}
