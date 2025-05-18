<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\ElectionResults;
use App\Models\Elections;

class ElectionResultsService
{
    // Implement your logic here
    public function fetchElectionResults($electionId, $currentSchool)
    {

       $electionresults = ElectionResults::where('school_branch_id', $currentSchool->id)
            ->where('election_id', $electionId)
            ->with(['ElectionRoles', 'Elections', 'electionCandidate'])
            ->get();
            return $electionresults;
    }

}
