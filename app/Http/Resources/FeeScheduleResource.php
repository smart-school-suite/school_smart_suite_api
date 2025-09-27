<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeeScheduleResource extends JsonResource
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
            'config_status' => $this->config_status,
            'status' => $this->status,
            'specialty_id' => $this->specialty->id,
            'specialty_name' => $this->specialty->specialty_name,
            'level_name' => $this->specialty->level->name,
            'level_id' => $this->specialty->level_id,
            'semester' => $this->schoolSemester->semester->name,
            'tuition_fee' => $this->specialty->school_fee,
            'school_semester_id' => $this->schoolSemester->id,
            'start_date' => $this->schoolSemester->start_date,
            'end_date' => $this->schoolSemester->end_date,
            'student_batch_id' => $this->schoolSemester->student_batch_id
        ];
    }
}
