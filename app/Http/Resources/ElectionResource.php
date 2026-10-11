<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class ElectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $now = Carbon::now();

        $appStart = $this->application_start ? Carbon::parse($this->application_start) : null;
        $appEnd   = $this->application_end ? Carbon::parse($this->application_end) : null;
        $voteStart = $this->voting_start ? Carbon::parse($this->voting_start) : null;
        $voteEnd   = $this->voting_end ? Carbon::parse($this->voting_end) : null;

        return [
            'id' => $this->id,
            'election_title' => $this->electionType?->election_title,
            'application_start' => $appStart?->format('F j, Y g:i A'),
            'application_end' => $appEnd?->format('F j, Y g:i A'),
            'vote_start' => $voteStart?->format('F j, Y g:i A'),
            'vote_end' => $voteEnd?->format('F j, Y g:i A'),
            'school_year' => $this->academicYear?->name,
            'status' => $this->getElectionStatus($now, $appStart, $voteEnd),
            'voting_status' => $this->getPhaseStatus($now, $voteStart, $voteEnd),
            'application_status' => $this->getPhaseStatus($now, $appStart, $appEnd),
            'results_published' => (bool) $this->is_results_published,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function getPhaseStatus(Carbon $now, ?Carbon $start, ?Carbon $end): string
    {
        if (!$start || !$end) {
            return 'pending';
        }

        if ($now->isBefore($start)) {
            return 'pending';
        }

        if ($now->betweenIncluded($start, $end)) {
            return 'ongoing';
        }

        return 'ended';
    }

    private function getElectionStatus(Carbon $now, ?Carbon $start, ?Carbon $end): string
    {
        if (!$start || !$end) {
            return 'upcoming';
        }

        if ($now->isBefore($start)) {
            return 'upcoming';
        }

        if ($now->betweenIncluded($start, $end)) {
            return 'ongoing';
        }

        return 'ended';
    }
}
