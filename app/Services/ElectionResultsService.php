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
        $election = Elections::where("school_branch_id", $currentSchool->id)
                            ->find($electionId);
        if($election->status == "finished"){

        }
        $electionResults = ElectionResults::where('school_branch_id', $currentSchool->id)
            ->where('election_id', $electionId)
            ->with([
                'electionCandidate.student.specialty:id,specialty_name',
                'electionCandidate.student.level:id,level_name',
                'electionCandidate.student:id,name,profile_picture',
              //  'electionCandidate:id,election_role_id,student_id,vote_count,status',
                'electionRoles:id,name'
            ])
            ->get();

        $formattedResults = [];

        foreach ($electionResults as $result) {
            $roleName = $result->electionCandidate->electionRole->name ?? 'Unknown Role';

            if (!isset($formattedResults[$roleName])) {
                $formattedResults[$roleName] = [];
            }

            $candidate = $result->electionCandidate;
            $student = $candidate->student;

            $formattedResults[$roleName][] = [
                'student_name' => $student->name ?? null,
                'student_profile_picture' => $student->profile_picture ?? null,
                'specialty_name' => $student->specialty->specialty_name ?? null,
                'level_name' => $student->level->level_name ?? null,
                'vote_count' => $result->vote_count,
                'status' => $result->status,
            ];
        }

        return [
             'election' => Elections::find($electionId),
             'election_result' => $formattedResults
        ];
    }
}
