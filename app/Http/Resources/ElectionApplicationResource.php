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
            'application_isApproved' => $this->isApproved,
            "application_manifesto" =>  $this->manifesto,
            "application_personal_vision" => $this->personal_vision,
            "application_commitment_statement" => $this->commitment_statement,
            "student_name" => $this->student->name,
            "election" => $this->title,
            "election_role" => $this->electionRole->name
        ];
    }
}
