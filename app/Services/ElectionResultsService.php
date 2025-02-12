<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\ElectionResults;
use App\Models\Elections;

class ElectionResultsService
{
    // Implement your logic here
    public function fetchElectionResults($election_id, $currentSchool)
    {

        $find_election = Elections::find($election_id);
        if (!$find_election) {
            ApiResponseService::error("Election Not found", null, 404);
        }

        if ($this->checkIfElectionHasExpired($find_election)) {
            $this->updateElection($currentSchool->id, $election_id);
            $this->updateResults($currentSchool->id, $election_id);
        }

        $election_results = ElectionResults::where('school_branch_id', $currentSchool->id)
            ->where('election_id', $election_id)
            ->with(['ElectionRoles', 'Elections', 'electionCandidate'])
            ->get();

        if ($election_results->isEmpty()) {
            ApiResponseService::error('Election Results seems to be empty', null, 204);
        }

        return $election_results;
    }
    private function checkIfElectionHasExpired($election)
    {
        $currentDateTime = Carbon::now();
        $electionStartDateTime = Carbon::createFromFormat('Y-m-d H:i', $election->election_start_date . ' ' . $election->starting_time);
        $electionEndDateTime = Carbon::createFromFormat('Y-m-d H:i', $election->election_end_date . ' ' . $election->ending_time);
        return $currentDateTime->isAfter($electionEndDateTime);
    }

    private function updateElection($schoolBranchId, $electionId)
    {
        $election = Elections::find($electionId);
        if ($election) {
            $election->is_results_published = true;
            $election->save();
        }
    }
    private function updateResults($schoolBranchId, $electionId)
    {
        $electionResults = ElectionResults::where('school_branch_id', $schoolBranchId)
            ->where('election_id', $electionId)
            ->get();
        if ($electionResults->isEmpty()) {
            return;
        }
        $groupedResults = $electionResults->groupBy('position_id');
        foreach ($groupedResults as $positionId => $results) {
            $winner = $results->sortByDesc('vote_count')->first();
            foreach ($results as $result) {
                $result->election_status = ($result->candidate_id === $winner->candidate_id) ? 'won' : 'lost';
                $result->save();
            }
        }
    }
}
