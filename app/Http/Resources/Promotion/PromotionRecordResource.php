<?php

namespace App\Http\Resources\Promotion;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromotionRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id ?? null,
            'outcome' => $this->outcome ?? null,
            'status' => $this->status ?? null,
            'processed_at' => $this->processed_at ?? null,
            'reviewed_at' => $this->reviewed_at ?? null,
            'admin_note' => $this->admin_note ?? null,
            'created_at' => $this->created_at ?? null,
            'updated_at' => $this->updated_at ?? null,
            'promtion_run' => $this->promotionRun ?? null,
            's_username' => $this->student->username ?? null,
            's_first_name' => $this->student->first_name ?? null,
            's_last_name' => $this->student->last_name ?? null,
            's_full_name' => $this->student->name ?? null,
            'promotion_run_id' => $this->promotionRun->id ?? null,
            'academic_year' => $this->promotionRun?->schoolYear?->systemAcademicYear?->name ?? null,
            'from_spec_name' => $this->fromSpecialty?->specialty_name ?? null,
            'from_level_name' => $this->fromSpecialty?->level?->name ?? null,
            'from_level_number' => $this->fromSpecialty?->level?->level ?? null,
            'to_spec_name' => $this->toSpecialty?->specialty_name ?? null,
            'to_level_name' => $this->toSpecialty?->level?->name ?? null,
            'to_level_number' => $this->toSpecialty?->level?->level ?? null,
        ];
    }
}
