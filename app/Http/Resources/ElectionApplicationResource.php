<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ElectionApplicationResource extends JsonResource
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
            'application_status' => $this->application_status,
            "application_manifesto" =>  $this->manifesto,
            "application_personal_vision" => $this->personal_vision,
            "application_commitment_statement" => $this->commitment_statement,
            "name" => $this->student?->name,
            "first_name" => $this->student?->first_name,
            "last_name" => $this->student?->last_name,
            "username" => $this->student?->username,
            "level" => $this->student?->specialty?->level?->level,
            "level_name" => $this->student?->specialty?->level?->name,
            "specialty" => $this->student?->specialty?->specialty_name,
            "election" => $this->election->electionType->election_title,
            "election_role" => $this->electionRole->name,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
