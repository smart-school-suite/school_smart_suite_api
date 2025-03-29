<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HodResource extends JsonResource
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
            'first_name' => $this->hodable->first_name,
            'full_names' => $this->hodable->name,
            'last_name' => $this->hodable->last_name,
            'department_name' => $this->department->department_name,
            'department_status' => $this->department->status,
            'department_description' => $this->department->description,
        ];
    }
}
