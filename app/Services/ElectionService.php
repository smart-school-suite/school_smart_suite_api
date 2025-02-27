<?php

namespace App\Services;

use App\Models\ElectionCandidates;
use App\Models\Elections;

class ElectionService
{
    // Implement your logic here

    public function createElection(array $data, $currentSchool)
    {
        $election = new Elections();
        $election->title  = $data["title"];
        $election->description = $data["description"];
        $election->election_start_date = $data["election_start_date"];
        $election->election_end_date = $data["election_end_date"];
        $election->starting_time = $data["starting_time"];
        $election->ending_time = $data["ending_time"];
        $election->school_year_start = $data["school_year_start"];
        $election->school_year_end = $data["school_year_end"];
        $election->school_branch_id = $currentSchool->id;
        $election->is_results_published = false;
        $election->save();
        return $election;
    }

    public function updateElection(array $data, $currentSchool, $election_id)
    {
        $election = Elections::where("school_branch_id", $currentSchool->id)->find($election_id);
        if (!$election) {
            return ApiResponseService::error("Election not found", null, 404);
        }
        $filterData = array_filter($data);
        $election->update($filterData);
        return $election;
    }

    public function deleteElection( $currentSchool, $election_id)
    {
        $election = Elections::where("school_branch_id", $currentSchool->id)->find($election_id);
        if (!$election) {
            return ApiResponseService::error("Election not found", null, 404);
        }
        $election->delete();
        return $election;
    }

    public function fetchElections($currentSchool)
    {
        $elections = Elections::where('school_branch_id', $currentSchool->id)->get();
        return $elections;
    }

    public function getElectionCandidates(string $electionId, $currentSchool){
          $getElectionCandidates = ElectionCandidates::where("school_branch_id", $currentSchool->id)
                                  ->where("election_id", $electionId)
                                  ->with(['student', 'electionApplication'])
                                  ->get();
          return $getElectionCandidates;
    }
}
