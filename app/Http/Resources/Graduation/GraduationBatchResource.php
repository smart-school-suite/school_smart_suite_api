<?php

namespace App\Http\Resources\Graduation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GraduationBatchResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'graduation_start_date' => $this->graduation_start_date,
            'graduation_end_date' => $this->graduation_end_date,
            'location' => $this->location,
            'school_year_id' => $this->school_year_id,
            'school_branch_id' => $this->school_branch_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'graduation_record_count' => $this->graduation_record_count ?? 0,

            'academic_year_name' => $this->academicYear?->name,
            'academic_year_start' => $this->academicYear?->year_start,
            'academic_year_end' => $this->academicYear?->year_end,
        ];
    }
}
