<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GraduationBatchDateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
             'id'  => $this->id,
             'graduation_date' => $this->graduation_date,
             'specialty_name' => $this->specialty->specialty_name,
             'level_name' => $this->level->name,
             'level' => $this->level->level,
        ];
    }
}
