<?php

namespace App\Services\Election;

use App\Exceptions\AppException;
use App\Models\Elections;
use App\Models\ElectionResults;
use App\Models\VoterStatus;

class ElectionResultService
{
    public function fetchElectionResults($electionId, $currentSchool)
    {

        $election = Elections::where("school_branch_id", $currentSchool->id)->find($electionId);

        if (is_null($election)) {
            throw new AppException(
                "Election not found",
                404,
                "Election Missing",
                "The election with ID $electionId could not be found in this school branch.",
                "/elections"
            );
        }

        if ($election->status == "finished") {
            throw new AppException(
                "Election results not yet published",
                403,
                "Results Unavailable",
                "The results for election ID $electionId are not yet finalized or published.",
                "/elections/" . $electionId
            );
        }

        $electionResults = ElectionResults::where('school_branch_id', $currentSchool->id)
            ->where('election_id', $electionId)
            ->with([
                'electionCandidate.student:id,name,profile_picture,specialty_id,level_id',
                'electionCandidate.student.specialty:id,specialty_name',
                'electionCandidate.student.level:id,level,name',
                'electionCandidate.electionRole:id,name',
            ])
            ->orderBy('position_id')
            ->orderByDesc('vote_count')
            ->get();

        if ($electionResults->isEmpty()) {
            throw new AppException(
                "No final results found for this election",
                404,
                "Results Missing",
                "The election has finished, but no results records were generated or found.",
                "/elections/" . $electionId
            );
        }

        $formattedResults = [];

        foreach ($electionResults as $result) {
            $roleName = $result->electionCandidate->electionRole->name ?? 'Unknown Role';
            $roleId = $result->position_id;

            if (!isset($formattedResults[$roleId])) {
                $formattedResults[$roleId] = [
                    'role_name' => $roleName,
                    'role_id' => $roleId,
                    'candidates' => []
                ];
            }

            $candidate = $result->electionCandidate;
            $student = $candidate->student;

            $isWinner = false;

            $formattedResults[$roleId]['candidates'][] = [
                'candidate_id' => $candidate->id,
                "position_id" => $roleId,
                'student_name' => $student->name ?? null,
                'student_profile_picture' => $student->profile_picture ?? null,
                'specialty_name' => $student->specialty->specialty_name ?? null,
                'level_name' => $student->level->name ?? null,
                'level' => $student->level->level ?? null,
                'vote_count' => $result->vote_count,
                'is_winner' => $isWinner,
            ];
        }

        foreach ($formattedResults as $roleId => &$roleGroup) {
            if (!empty($roleGroup['candidates'])) {
                $maxVotes = $roleGroup['candidates'][0]['vote_count'];
                foreach ($roleGroup['candidates'] as &$candidate) {
                    if ($candidate['vote_count'] === $maxVotes) {
                        $candidate['is_winner'] = true;
                    }
                }
            }
        }
        unset($roleGroup);

        $finalResults = array_values($formattedResults);


        return [
            'election' => $election->load(['electionType']),
            'election_result' => $finalResults
        ];
    }

    public function getLiveElectionResults($electionId, $currentSchool, $authUser)
    {
        $election = Elections::where("school_branch_id", $currentSchool->id)->find($electionId);

        if (is_null($election)) {
            throw new AppException(
                "Election not found",
                404,
                "Election Missing",
                "The election with ID $electionId could not be found in this school branch.",
                "/elections"
            );
        }

       // if ($election->status == "not_started") {
           // throw new AppException(
             //   "Election has not yet started",
               // 403,
               // "Live Results Unavailable",
                //"The election with ID $electionId has not yet begun.",
                //"/elections/" . $electionId
            //);
       // }


        $liveResults = ElectionResults::where('school_branch_id', $currentSchool->id)
            ->where('election_id', $electionId)
            ->with([
                'electionCandidate.student:id,name,profile_picture,specialty_id,level_id',
                'electionCandidate.student.specialty:id,specialty_name',
                'electionCandidate.student.level:id,level,name',
                'electionCandidate.electionRole:id,name',
            ])
            ->orderBy('position_id')
            ->orderByDesc('vote_count')
            ->get();

       // if ($liveResults->isEmpty() && $election->status === 'finished') {
         //   throw new AppException(
           //     "No results found for this election",
             //   404,
               // "Results Missing",
               // "The election has finished, but no results records were generated or found.",
               // "/elections/" . $electionId
            //);
       // }

        $userVotes = VoterStatus::where("school_branch_id", $currentSchool->id)
            ->where("election_id", $electionId)
            ->where("votable_id", $authUser['userId'])
            ->pluck('candidate_id', 'position_id')
            ->toArray();

        $formattedResults = [];

        foreach ($liveResults as $result) {
            $roleName = $result->electionCandidate->electionRole->name ?? 'Unknown Role';
            $roleId = $result->position_id;
            $candidateId = $result->electionCandidate->id;

            if (!isset($formattedResults[$roleId])) {
                $formattedResults[$roleId] = [
                    'role_name' => $roleName,
                    'role_id' => $roleId,
                    'has_user_voted' => isset($userVotes[$roleId]),
                    'candidates' => []
                ];
            }

            $candidate = $result->electionCandidate;
            $student = $candidate->student;

            $hasUserVotedForCandidate = (
                isset($userVotes[$roleId]) &&
                $userVotes[$roleId] == $candidateId
            );

            $formattedResults[$roleId]['candidates'][] = [
                'candidate_id' => $candidateId,
                "position_id" => $roleId,
                'student_name' => $student->name ?? null,
                'student_profile_picture' => $student->profile_picture ?? null,
                'specialty_name' => $student->specialty->specialty_name ?? null,
                'level_name' => $student->level->name ?? null,
                'level' => $student->level->level ?? null,
                'vote_count' => $result->vote_count,
                'user_voted_for_candidate' => $hasUserVotedForCandidate,
            ];
        }
        $finalResults = array_values($formattedResults);
        return [
            'election' => $election->load(['electionType']),
            'election_result' => $finalResults
        ];
    }
}
