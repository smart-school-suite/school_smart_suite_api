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
        $slots = $this->feeScheduleSlot ?? $this->fee_schedule_slots ?? null;

        $isConfigured = false;
        if ($slots !== null) {
            $isConfigured = $slots instanceof \Illuminate\Support\Collection
                ? $slots->isNotEmpty()
                : !empty($slots);
        }

        $specialty = $this->schoolYear?->specialty;

        return [
            'id' => $this->id,
            'config_status' => $isConfigured,
            'status' => $this->status ?? 'active',
            'specialty_name' => $specialty?->specialty_name,
            'department_name' => $specialty?->department?->department_name,
            'level_name' => $specialty?->level?->name,
            'level_number' => $specialty?->level?->level,
            'tuition_fee' => (float) ($specialty?->school_fee ?? 0),
            'start_date' => $this->schoolYear?->start_date ?? $this->schoolYear?->start_date,
            'end_date' => $this->schoolYear?->end_date ?? $this->schoolYear?->end_date,
            'academic_year' => $this->schoolYear?->systemAcademicYear?->name,
            'created_at' => $this->created_at ?? null,
            'updated_at' => $this->updated_at ?? null
         ];
    }
}
