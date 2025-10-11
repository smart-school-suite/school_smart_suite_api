<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ElectionRoleResource extends JsonResource
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
            'role_title' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'election_type_name' => $this->electionType->election_title
        ];
    }
}
