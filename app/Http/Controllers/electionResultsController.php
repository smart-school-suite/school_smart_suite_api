<?php

namespace App\Http\Controllers;

use App\Models\ElectionResults;
use App\Models\Elections;
use Carbon\Carbon;
use Illuminate\Http\Request;

class electionResultsController extends Controller
{
    //

    public function fetchElectionResults(Request $request)
    {
        $election_id = $request->route('election_id');
        $currentSchool = $request->attributes->get('currentSchool');

        // Check if election exists
        $find_election = Elections::find($election_id);
        if (!$find_election) {
            return response()->json([
                'status' => 'error',
                'message' => 'Election not found'
            ], 404);
        }

        // Check if election has expired
        if ($this->checkIfElectionHasExpired($find_election)) {
            // Update election to publish results if it has expired
            $this->updateElection($currentSchool->id, $election_id);
            // Update election results based on votes
            $this->updateResults($currentSchool->id, $election_id);
        }

        // Fetch election results
        $election_results = ElectionResults::where('school_branch_id', $currentSchool->id)
            ->where('election_id', $election_id)
            ->with(['ElectionRoles', 'Elections', 'electionCandidate'])
            ->get();

        if ($election_results->isEmpty()) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Election results not available or seem to be empty'
            ], 204);
        }

        return response()->json([
            'status' => 'ok',
            'message' => "Election results fetched successfully",
            'election_results' => $election_results
        ], 200);
    }

    private function checkIfElectionHasExpired($election)
    {
        // Get the current time
        $currentDateTime = Carbon::now();

        // Combine date and time for start and end
        $electionStartDateTime = Carbon::createFromFormat('Y-m-d H:i', $election->election_start_date . ' ' . $election->starting_time);
        $electionEndDateTime = Carbon::createFromFormat('Y-m-d H:i', $election->election_end_date . ' ' . $election->ending_time);

        // Check if the current date and time is after the end of the election
        return $currentDateTime->isAfter($electionEndDateTime);
    }

    private function updateElection($schoolBranchId, $electionId)
    {
        $election = Elections::find($electionId);
        if ($election) {
            $election->is_results_published = true; // Set results published to true
            $election->save();
        }
    }

    private function updateResults($schoolBranchId, $electionId)
    {
        // Fetch all election results for the specific school branch and election ID
        $electionResults = ElectionResults::where('school_branch_id', $schoolBranchId)
            ->where('election_id', $electionId)
            ->get();

        if ($electionResults->isEmpty()) {
            return; // No results to update
        }

        // Group candidates by position
        $groupedResults = $electionResults->groupBy('position_id');

        // Process each group of candidates by position_id
        foreach ($groupedResults as $positionId => $results) {
            // Find the candidate with the highest votes in this position group
            $winner = $results->sortByDesc('vote_count')->first();

            // Update each candidate's election status
            foreach ($results as $result) {
                $result->election_status = ($result->candidate_id === $winner->candidate_id) ? 'won' : 'lost';
                $result->save();
            }
        }
    }
}
