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
            'specialty_name' => $this->specialty->specialty_name ?? null,
            'department_name' => $this->department->department_name ?? null,
            'semester_title' => $this->semester->name ?? null,
            'level_name' => $this->level->name ?? null,
            'level_number' => $this->level->level ?? null,
            'status' => $this->status
        ];
    }
}
