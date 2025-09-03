<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamResource extends JsonResource
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
            'semester_name' => $this->semester->name ?? null,
            "batch_title" => $this->studentBatch->name ?? null,
            "batchId" => $this->studentBatch->id ?? null,
            'specailty_name' => $this->specialty->specialty_name ?? null,
            'specialty_id' => $this->specialty->id ?? null,
            'level_name' => $this->level->name ?? null,
            'level_id' => $this->level->id ?? null,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'timetable_published' => $this->timetable_published ? 'created' : 'not created',
            'school_year' => $this->school_year,
            'weighted_mark' => $this->weighted_mark,
            'grading_added' => $this->grading_added
        ];
    }
}
