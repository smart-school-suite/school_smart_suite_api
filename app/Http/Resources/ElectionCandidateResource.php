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
            'student_name' => $this->student->name ?? null,
            'specialty' => $this->student->specialty->specialty_name,
            'level_name' => $this->student->specialty->level->name,
            'election_role' => $this->electionRole->name,
            'election_application_id' => $this->electionApplication->id,
            'status' => $this->isActive ? 'active' : 'inactive',
            'manifesto' => $this->electionApplication->manifesto,
            'personal_vision' => $this->electionApplication->personal_vision,
            'commitment_statement' => $this->electionApplication->commitment_statement,
            'election_type' => $this->electionRole->electionType->election_title
        ];
    }
}
