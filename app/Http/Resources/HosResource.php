<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HosResource extends JsonResource
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
            'first_name' => $this->hosable->first_name,
            'full_names' => $this->hosable->name,
            'last_name' => $this->hosable->last_name,
            'specialty_name' => $this->specialty->specialty_name,
            'specialty_status' => $this->specialty->status,
            'level_name' => $this->specialty->level->name,
            'level' => $this->specialty->level->level,
        ];
    }
}
