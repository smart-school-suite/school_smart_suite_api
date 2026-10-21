<?php

namespace App\Http\Resources\Graduation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlumniResource extends JsonResource
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
            'graduation_batch' => $this->graduationRecord?->batch?->name ?? null,
            'graduation_date' => $this->graduationRecord?->batch?->graduation_end_date ?? null,
            'specialty' => $this->graduationRecord?->specialty?->specialty_name ?? null,
            'level_name' => $this->graduationRecord?->specialty?->level->name ?? null,
            'level_number' => $this->graduationRecord?->specialty?->level->level ?? null,
            's_full_names' => $this->graduationRecord?->student?->name ?? null,
            's_last_name' => $this->graduationRecord?->student?->last_name ?? null,
            's_first_name' => $this->graduationRecord?->student?->first_name ?? null,
            's_username' => $this->graduationRecord?->student?->username ?? null,
            's_profile_picture' => $this->graduationRecord?->student?->profile_picture ?? null,
            'admin_notes' => $this->admin_notes ?? null,
            'academic_year' => $this->graduationRecord?->promotionRun?->schoolYear?->systemAcademicYear?->name ?? null,
            'created_at' => $this->created_at ?? null,
            'updated_at' => $this->updated_at ?? null
        ];
    }
}
