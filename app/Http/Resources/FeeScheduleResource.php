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
            'specialty_name' => $this->specialty->specialty_name,
            'level_name' => $this->specialty->level->name,
            'semester' => $this->schoolSemester->semester->name
        ];
    }
}
