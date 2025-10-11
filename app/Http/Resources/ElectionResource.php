<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
class ElectionResource extends JsonResource
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
            'election_title' => $this->electionType->election_title,
            'application_start' => Carbon::parse($this->application_start)->format('F j, Y g:i A'),
            'application_end' => Carbon::parse($this->application_end)->format('F j, Y g:i A'),
            'vote_start' => Carbon::parse($this->voting_start)->format('F j, Y g:i A'),
            'vote_end' => Carbon::parse($this->voting_end)->format('F j, Y g:i A'),
            'school_year' => $this->school_year,
            'status' => $this->status,
            'voting_status' => $this->voting_status,
            'application_status' => $this->application_status,
            'results_published' => $this->is_results_published
        ];
    }
}
