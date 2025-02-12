<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
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
            'course_code' => $this->course_code,
            'course_title' => $this->course_title,
            'credit' => $this->credit,
            'specialty' => $this->specialty->specialty_name ?? null,
            'department' => $this->department->department_name ?? null,
            'semester_id' => $this->semester_id,
            'level_id' => $this->level_id,
            'specialty_id' => $this->specialty_id,
            'department_id' => $this->department_id,
            'school_branch_id' => $this->school_branch_id,
        ];
    }
}
