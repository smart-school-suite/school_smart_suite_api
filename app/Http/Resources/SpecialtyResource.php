<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SpecialtyResource extends JsonResource
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
            'specialty_name' => $this->specialty_name,
            'registration_fee' =>  $this->registration_fee,
            'tuition_fee' =>  $this->school_fee,
            'total' => $this->school_fee + $this->registration_fee,
            'level_name' => $this->level->name ?? null,
            'level' => $this->level->level ?? null,
            'status' => $this->status,
            'description' => $this->description,
            'hos_name' => $this->hos->first()->hosable->name ?? null,
            'updated_at' => $this->updated_at->toDateTimeString(),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
