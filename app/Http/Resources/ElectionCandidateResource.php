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
            'student_name' => $this->student->student->name,
            'election_application_id' => $this->electionApplication->id,
            'isApproved' => $this->electionApplication->isApproved,
            'manifesto' => $this->electionApplication->manifesto,
            'personal_vision' => $this->electionApplication->personal_vision,
            'commitment_statement' => $this->electionApplication->commitment_statement,
            'isActive' => $this->isActive,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

        ];
    }
}
