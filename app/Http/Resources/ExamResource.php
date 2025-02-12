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
            'semester_name' => $this->semester->name,
            'specailty_name' => $this->specialty->specialty_name ?? null,
            'level_name' => $this->level->name ?? null,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'school_year' => $this->school_year,
            'weighted_mark' => $this->weighted_mark
        ];
    }
}
