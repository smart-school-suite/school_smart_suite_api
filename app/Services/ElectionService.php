<?php

namespace App\Services;

use App\Models\CurrentElectionWinners;
use App\Models\ElectionCandidates;
use App\Models\ElectionParticipants;
use App\Models\ElectionResults;
use App\Models\Elections;
use App\Models\ElectionType;
use App\Models\PastElectionWinners;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ElectionService
{
    // Implement your logic here

    public function createElection(array $data, $currentSchool)
    {
        $election = new Elections();
        $election->application_start = $data["application_start"];
        $election->application_end = $data["application_end"];
        $election->voting_start = $data["voting_start"];
        $election->voting_end = $data["voting_end"];
        $election->school_year = $data["school_year"];
        $election->election_type_id = $data["election_type_id"];
        $election->school_branch_id = $currentSchool->id;
        $election->save();
        return $election;
    }

    public function updateElection(array $data, $currentSchool, $electionId)
    {
        $election = Elections::where("school_branch_id", $currentSchool->id)->find($electionId);
        if (!$election) {
            return ApiResponseService::error("Election not found", null, 404);
        }
        $filterData = array_filter($data);
        $election->update($filterData);
        return $election;
    }

    public function bulkUpdateElection(array $electionList)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($electionList as $election) {
                $schoolElection = Elections::findOrFail($election['election_id']);
                $filterData = array_filter($election);
                $schoolElection->update($filterData);
                $result[] = $schoolElection;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function bulkDeleteElection($electionIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($electionIds as $electionId) {
                $election = Elections::findOrFail($electionId['election_id']);
                $election->delete();
                $result[] = $election;
            }
            DB::commit();
           return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteElection($currentSchool, $electionId)
    {
        $election = Elections::where("school_branch_id", $currentSchool->id)->find($electionId);
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

    public function getElectionCandidates(string $electionId, $currentSchool)
    {
        $getElectionCandidates = ElectionCandidates::where("school_branch_id", $currentSchool->id)
            ->where("election_id", $electionId)
            ->with(['student', 'electionApplication'])
            ->get();
        return $getElectionCandidates;
    }

    public function addAllowedElectionParticipants(array $electionParticipantsList, $currentSchool)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($electionParticipantsList as $electionParticipant) {
                $allowedParticipants = ElectionParticipants::create([
                    'specialty_id' => $electionParticipant['specialty_id'],
                    'election_id' => $electionParticipant['election_id'],
                    'level_id' => $electionParticipant['level_id'],
                    'school_branch_id' => $currentSchool->id
                ]);
                $result[] = $allowedParticipants;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getAllowedElectionParticipants($currentSchool, $electionId)
    {
        $electionParticipants = ElectionParticipants::where("school_branch_id", $currentSchool->id)
            ->where("election_id", $electionId)
            ->with(['Specialty', 'level'])
            ->get();
        return $electionParticipants;
    }

    public function addAllowedParticipantsByOtherElection($currentSchool, $electionId, $targetElectionId)
    {
        $result = [];
        try {
            DB::beginTransaction();
            $electionParticipants = ElectionParticipants::where("election_id", $targetElectionId)->get();
            foreach ($electionParticipants as $electionParticipant) {
                $allowedParticipants = ElectionParticipants::create([
                    'specialty_id' => $electionParticipant['specialty_id'],
                    'election_id' => $electionId,
                    'level_id' => $electionParticipant['level_id'],
                    'school_branch_id' => $currentSchool->id
                ]);
                $result[] = $allowedParticipants;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getCurrentElectionWinners($currentSchool)
    {
        $currentElectionWinners = CurrentElectionWinners::where("school_branch_id", $currentSchool->id)
            ->with(['election', 'electionRole', 'student'])
            ->get();
        return $currentElectionWinners;
    }

    public function createElectionType($electionTypeData, $currentSchool)
    {
        $electionType = ElectionType::create([
            'election_title' => $electionTypeData['election_title'],
            'description' => $electionTypeData['description'],
            'school_branch_id' => $currentSchool->id
        ]);
        return $electionType;
    }

    public function UpdateElectionType($updateData, $electionTypeId){
        $electionType = ElectionType::findOrFail($electionTypeId);
        $cleanedData = array_filter($updateData);
        $electionType->update($cleanedData);
        return $electionType;
    }
    public function getElectionType($currentSchool){
        $electionTypes = ElectionType::where("school_branch_id", $currentSchool->id)->get();
        return $electionTypes;
    }

    public function deleteElectionType($electionTypeId){
        $electionType = ElectionType::findOrFail($electionTypeId);
        $electionType->delete();
        return $electionType;
    }
    public function getActiveElectionType($currentSchool){
        $electionTypes = ElectionType::where("school_branch_id", $currentSchool->id)->where("status", "active")->get();
        return $electionTypes;
    }
    public function deactivateElectionType($electionTypeId)
    {
        $electionType = ElectionType::findOrFail($electionTypeId);
        $electionType->status = 'inactive';
        $electionType->save();
        return $electionType;
    }

    public function bulkDeactivateElectionType($electionTypeIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($electionTypeIds as $electionTypeId) {
                $electionType = ElectionType::findOrFail($electionTypeId);
                $electionType->status = 'inactive';
                $electionType->save();
                $result[] = [
                    $electionType
                ];
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function bulkActivateElectionType($electionTypeIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($electionTypeIds as $electionTypeId) {
                $electionType = ElectionType::findOrFail($electionTypeId);
                $electionType->status = 'active';
                $electionType->save();
                $result[] = [
                    $electionType
                ];
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function activateElectionType($electionTypeId)
    {
        $electionType = ElectionType::findOrFail($electionTypeId);
        $electionType->status = 'active';
        $electionType->save();
        return $electionType;
    }

    public function getPastElectionWinners($currentSchool)
    {
        $pastElectionWinners = PastElectionWinners::where("school_branch_id", $currentSchool->id)
            ->with(['election', 'electionRole', 'student'])
            ->get();
        return $pastElectionWinners;
    }


}
