<?php

namespace App\Http\Resources\Graduation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GraduationRecordResource extends JsonResource
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
            'reviewed_at' => $this->reviewed_at,
            'status' => $this->status,
            'eligibility_status' => $this->eligibility_status,
            'clearance_status' => $this->clearance_status,
            'admin_note' => $this->admin_note,
            'batch' => $this->batch->name,
            'level_name' => $this->specialty?->level?->name ?? null,
            'level_number' => $this->specialty?->level?->level ?? null,
            'specialty' => $this->specialty->specialty_name ?? null,
            's_full_name' => $this->student->name,
            's_first_name' => $this->student->first_name,
            's_last_name' => $this->student->last_name,
            's_username' => $this->student->name,
            's_profile_picture' => $this->student->profile_picture,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
