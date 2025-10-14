<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ElectionResultResource extends JsonResource
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
            'profile_picture' => $this->student->profile_picture ?? null,
            'specialty_name' => $this->student->specialty->specialty_name ?? null,
            'level_name' => $this->student->specialty->level->name ?? null,
            'level' => $this->student->specialty->level->level ?? null,
            'election_role' => $this->electionRole->name ?? null,
            'election_role_id' => $this->electionRole->id ?? null,
            'election_name' => $this->electionType->election_title ?? null,
            'election_id' => $this->election_id ?? null,
            'school_year' => $this->election->school_year ?? null,
            'vote_count' => $this->total_votes ?? null
        ];
    }
}
