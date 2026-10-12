<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ElectionCandidateResource extends JsonResource
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
            'name' => $this->student->name ?? null,
            'username' => $this->student->username ?? null,
            'first_name' => $this->student->first_name ?? null,
            'last_name' => $this->student->last_name ?? null,
            'specialty' => $this->student->specialty->specialty_name,
            'level_name' => $this->student->specialty->level->name,
            'level' => $this->student->specialty->level->level,
            'election_role' => $this->electionRole->name,
            'election_application_id' => $this->electionApplication->id,
            'status' => $this->isActive ? 'active' : 'disqualified',
            'manifesto' => $this->electionApplication->manifesto,
            'personal_vision' => $this->electionApplication->personal_vision,
            'commitment_statement' => $this->electionApplication->commitment_statement,
            'election_type' => $this->electionRole->electionType->election_title,
            'created_at' => $this->created_at ?? null,
            'updated_at' => $this->updated_at ?? null
        ];
    }
}
