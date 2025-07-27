<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResitExamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=> $this->id,
            'exam_name' => $this->examtype->exam_name,
            'exam_type' => $this->examtype->type,
            'exam_type_id' => $this->examtype->id,
            'semester_name' => $this->semester->name ?? null,
            'semester_id' => $this->semester->id ?? null,
            'specailty_name' => $this->specialty->specialty_name ?? null,
            'level_name' => $this->level->name ?? null,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'school_year' => $this->school_year,
            'reference_exam_id' => $this->reference_exam_id,
            'timetable_published' => $this->timetable_published,
            'weighted_mark' => $this->weighted_mark,
            'grading_added' => $this->grading_added
        ];
    }
}
