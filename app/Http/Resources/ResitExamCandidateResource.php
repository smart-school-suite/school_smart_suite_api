<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResitExamCandidateResource extends JsonResource
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
            'is_student_evaluated' =>  $this->resitScores?->isNotEmpty() ?? false,
            'student_name' => $this->student?->name,
            'level_number' => $this->resitExam?->schoolYear?->specialty?->level?->level,
            'level_name' => $this->resitExam?->schoolYear?->specialty?->level?->name,
            'specialty_name' => $this->resitExam?->schoolYear?->specialty?->specialty_name,
            'semester' => $this->resitExam?->examType?->semesters?->name,
            'exam_name' => $this->resitExam?->examType?->exam_name,
            'exam_type' => $this->resitExam?->examType?->type,
            'academic_year' => $this->resitExam?->schoolYear?->systemAcademicYear?->name,
            "created_at" => $this->created_at ?? null,
            "updated_at" => $this->updated_at ?? null
        ];
    }
}
