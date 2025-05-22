<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentDropOutResource extends JsonResource
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
            'student_id' => $this->student_id,
            'reason' => $this->reason,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'student_name' => $this->name,
            'level' => $this->level->level,
            'level_name' => $this->level->name,
            'specialty_title' => $this->specialty->specialty_name,
            'department_name' => $this->department->department_name,
        ];
    }
}
