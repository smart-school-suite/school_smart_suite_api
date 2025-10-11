<?php

namespace App\Services;

use App\Jobs\DataCleanupJobs\UpdateElectionResultStatus;
use App\Jobs\DataCleanupJobs\UpdateElectionStatusJob;
use App\Jobs\NotificationJobs\SendAdminElectionConcludedNotificationJob;
use App\Jobs\NotificationJobs\SendElectionConcludedNotificationJob;
use App\Jobs\NotificationJobs\SendElectionOpenNotificationJob;
use App\Jobs\NotificationJobs\SendElectionVoteOpenNotification;
use App\Jobs\StatisticalJobs\OperationalJobs\ElectionStatJob;
use App\Models\CurrentElectionWinners;
use App\Models\ElectionCandidates;
use App\Models\ElectionParticipants;
use App\Models\Elections;
use App\Models\ElectionType;
use App\Models\PastElectionWinners;
use App\Models\Student;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ElectionService
{
    // Implement your logic here
    public function createElection(array $data, $currentSchool)
    {
        $election = new Elections();
        $electionId = Str::uuid();
        $election->id = $electionId;
        $election->application_start = $data["application_start"];
        $election->application_end = $data["application_end"];
        $election->voting_start = $data["voting_start"];
        $election->voting_end = $data["voting_end"];
        $election->school_year = $data["school_year"];
        $election->election_type_id = $data["election_type_id"];
        $election->school_branch_id = $currentSchool->id;
        $election->save();
        $this->dispatchJobs($electionId, $currentSchool, $data);
        return $election;
    }

    public function getElectionDetails($currentSchool, $electionId){
        $electionDetails = Elections::where("school_branch_id", $currentSchool->id)
                                    ->with(['electionType'])
                                     ->find($electionId);
        return $electionDetails;
    }
    private function dispatchJobs($electionId, $currentSchool, $data){
        ElectionStatJob::dispatch($electionId, $currentSchool->id);
        SendElectionOpenNotificationJob::dispatch($electionId, $currentSchool->id)->delay(Carbon::parse($data["application_start"]));
        SendElectionVoteOpenNotification::dispatch($electionId, $currentSchool->id)->delay(Carbon::parse($data["voting_start"]));
        UpdateElectionStatusJob::dispatch($electionId, $currentSchool->id)->delay(Carbon::parse($data["application_end"]));
        UpdateElectionStatusJob::dispatch($electionId, $currentSchool->id)->delay(Carbon::parse($data["application_start"]));
        UpdateElectionStatusJob::dispatch($electionId, $currentSchool->id)->delay(Carbon::parse($data["voting_start"]));
        UpdateElectionStatusJob::dispatch($electionId, $currentSchool->id)->delay(Carbon::parse($data["voting_end"]));
        UpdateElectionResultStatus::dispatch($electionId, $currentSchool->id)->delay(Carbon::parse($data["voting_end"]));
        SendAdminElectionConcludedNotificationJob::dispatch($electionId, $currentSchool->id)->delay(Carbon::parse($data["voting_end"]));
        SendElectionConcludedNotificationJob::dispatch($electionId, $currentSchool->id)->delay(Carbon::parse($data["voting_end"]));
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
    public function getElections($currentSchool)
    {
        $elections = Elections::where('school_branch_id', $currentSchool->id)
                                ->with(['electionType'])
                                ->get();
        return $elections;
    }
    public function upcomingElectionByStudent($currentSchool, $studentId)
    {
        $student = Student::where('school_branch_id', $currentSchool->id)
            ->find($studentId);

        if (!$student) {
            return collect();
        }
        $upcomingElections = Elections::where('school_branch_id', $currentSchool->id)
            ->where('voting_start', '<=', now())
            ->where('voting_end', '>=', now())
            ->where('status', 'pending')
            ->with(['electionType'])
            ->get();

        $eligibleElections = $upcomingElections->filter(function ($election) use ($student, $currentSchool) {
            $isParticipantEligible = ElectionParticipants::where('election_id', $election->id)
                ->where('school_branch_id', $currentSchool->id)
                ->where('specialty_id', $student->specialty_id)
                ->where('level_id', $student->level_id)
                ->exists();

            return $isParticipantEligible;
        });

        return $eligibleElections;
    }
    public function getElectionCandidatesByElection(string $electionId, $currentSchool)
    {
        $getElectionCandidates = ElectionCandidates::where("school_branch_id", $currentSchool->id)
            ->where("election_id", $electionId)
            ->with(['student', 'electionApplication'])
            ->get();
        return $getElectionCandidates;
    }

    public function getElectionCandidates($currentSchool){
        $getElectionCandidates = ElectionCandidates::where("school_branch_id", $currentSchool->id)
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
         $existingType = ElectionType::where("school_branch_id", $currentSchool->id)
                        ->where("election_title", $electionTypeData['election_title'])
                        ->exists();
        $electionType = ElectionType::create([
            'election_title' => $electionTypeData['election_title'],
            'description' => $electionTypeData['description'],
            'school_branch_id' => $currentSchool->id
        ]);
        return $electionType;
    }
    public function UpdateElectionType($updateData, $electionTypeId)
    {
        $electionType = ElectionType::findOrFail($electionTypeId);
        $cleanedData = array_filter($updateData);
        $electionType->update($cleanedData);
        return $electionType;
    }
    public function getElectionType($currentSchool)
    {
        $electionTypes = ElectionType::where("school_branch_id", $currentSchool->id)->get();
        return $electionTypes;
    }
    public function deleteElectionType($electionTypeId)
    {
        $electionType = ElectionType::findOrFail($electionTypeId);
        $electionType->delete();
        return $electionType;
    }
    public function getActiveElectionType($currentSchool)
    {
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
