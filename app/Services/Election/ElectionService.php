<?php

namespace App\Services\Election;

use App\Jobs\DataCleanupJobs\UpdateElectionResultStatus;
use App\Jobs\DataCleanupJobs\UpdateElectionStatusJob;
use App\Jobs\NotificationJobs\SendAdminElectionConcludedNotificationJob;
use App\Jobs\NotificationJobs\SendElectionConcludedNotificationJob;
use App\Jobs\NotificationJobs\SendElectionOpenNotificationJob;
use App\Jobs\NotificationJobs\SendElectionVoteOpenNotification;
use App\Jobs\StatisticalJobs\OperationalJobs\ElectionStatJob;
use App\Models\ElectionParticipants;
use App\Models\Elections;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;
use App\Exceptions\AppException;
use Exception;
use App\Events\Actions\AdminActionEvent;

class ElectionService
{
    public function createElection(array $data, $currentSchool, $authAdmin)
    {
        try {
            $existingElection = Elections::where("school_branch_id", $currentSchool->id)
                ->where("school_year", $data["school_year"])
                ->where("election_type_id", $data['election_type_id'])
                ->exists();

            if ($existingElection) {
                throw new AppException(
                    "Election already exists",
                    409,
                    "Duplicate Election Found",
                    "An election of this type has already been created for the current school year (" . $data["school_year"] . ").",
                    "/elections/create"
                );
            }

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
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.election.create"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "electionManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $election,
                    "message" => "Election Created",
                ]
            );
            return $election;
        } catch (Throwable $e) {
            if (!($e instanceof AppException)) {
                throw new AppException(
                    "Failed to create election",
                    500,
                    "Creation Error",
                    "An unexpected error occurred while attempting to save the new election.",
                    "/elections/create"
                );
            }
            throw $e;
        }
    }
    private function dispatchJobs($electionId, $currentSchool, $data)
    {
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
    public function bulkDeleteElection($electionIds, $currentSchool, $authAdmin)
    {
        $result = [];
        try {
            DB::beginTransaction();

            $foundAll = true;
            $missingId = null;

            foreach ($electionIds as $electionItem) {
                $electionId = $electionItem['election_id'] ?? null;
                if (is_null($electionId)) {
                    throw new AppException(
                        "One or more provided election items is malformed",
                        400,
                        "Invalid Input Structure",
                        "The list of election IDs contains an item that is null or missing the 'election_id' key.",
                        "/elections"
                    );
                }
                if (!Elections::where('id', $electionId)->exists()) {
                    $foundAll = false;
                    $missingId = $electionId;
                    break;
                }
            }

            if (!$foundAll) {
                DB::rollBack();
                throw new AppException(
                    "One or more elections not found for deletion",
                    404,
                    "Election Missing in Bulk",
                    "The election with ID $missingId could not be found, halting bulk deletion.",
                    "/elections"
                );
            }

            foreach ($electionIds as $electionItem) {
                $electionId = $electionItem['election_id'];
                $election = Elections::where("school_branch_id", $currentSchool->id)->find($electionId);
                $election->delete();
                $result[] = $election;
            }

            if (empty($result) && !empty($electionIds)) {
                DB::rollBack();
                throw new AppException(
                    "Deletion failed to process any election, despite valid input",
                    500,
                    "Processing Error",
                    "A system error prevented the deletion of the selected elections.",
                    "/elections"
                );
            }

            DB::commit();
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.election.delete"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "electionManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $electionIds,
                    "message" => "Election Deleted",
                ]
            );
            return $result;
        } catch (Throwable $e) {
            DB::rollBack();

            if ($e instanceof AppException) {
                throw $e;
            }
            throw new AppException(
                "Bulk deletion failed due to a system error",
                500,
                "System Error",
                "An unexpected error occurred during the bulk deletion process.",
                "/elections"
            );
        }
    }
    public function deleteElection($currentSchool, $electionId, $authAdmin)
    {
        $election = Elections::where("school_branch_id", $currentSchool->id)->find($electionId);

        if (is_null($election)) {
            throw new AppException(
                "Election not found for deletion",
                404,
                "Election Missing",
                "The election with ID $electionId could not be found in this school branch.",
                "/elections"
            );
        }

        try {
            $election->delete();
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.election.delete"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "electionManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $election,
                    "message" => "Election Deleted",
                ]
            );
            return $election;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to delete election",
                500,
                "Deletion Error",
                "An unexpected error occurred while attempting to delete the election.",
                "/elections"
            );
        }
    }
    public function getElections($currentSchool)
    {
        $elections = Elections::where('school_branch_id', $currentSchool->id)
            ->where("status", "!=", "finished")
            ->with(['electionType'])
            ->get();

        if ($elections->isEmpty()) {
            throw new AppException(
                "No elections found",
                404,
                "Elections Missing",
                "There are no elections available for this school branch.",
                "/elections"
            );
        }

        return $elections;
    }
    public function getElectionDetails($currentSchool, $electionId)
    {
        $electionDetails = Elections::where("school_branch_id", $currentSchool->id)
            ->with(['electionType'])
            ->find($electionId);

        if (is_null($electionDetails)) {
            throw new AppException(
                "Election not found",
                404,
                "Election Details Missing",
                "The election with ID $electionId could not be found for this school branch.",
                "/elections"
            );
        }

        return $electionDetails;
    }
    public function updateElection(array $data, $currentSchool, $electionId, $authAdmin)
    {
        $election = Elections::where("school_branch_id", $currentSchool->id)->find($electionId);

        if (is_null($election)) {
            throw new AppException(
                "Election not found for update",
                404,
                "Election Missing",
                "The election with ID $electionId could not be found in this school branch for updating.",
                "/elections"
            );
        }

        $filterData = array_filter($data);

        if (empty($filterData)) {
            throw new AppException(
                "No valid data provided for update",
                400,
                "Invalid Update Data",
                "The request contained no valid, non-empty fields to update the election.",
                "/elections/" . $electionId . "/edit"
            );
        }

        try {
            $election->update($filterData);
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.election.update"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "electionManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $election,
                    "message" => "Election Updated",
                ]
            );
            return $election;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to update election",
                500,
                "Update Error",
                "An unexpected error occurred while attempting to save the changes to the election.",
                "/elections/" . $electionId . "/edit"
            );
        }
    }
    public function bulkUpdateElection(array $electionList, $currentSchool, $authAdmin)
    {
        $result = [];
        try {
            DB::beginTransaction();

            foreach ($electionList as $electionData) {
                $electionId = $electionData['election_id'] ?? null;
                $filterData = array_filter($electionData);

                if (is_null($electionId)) {
                    DB::rollBack();
                    throw new AppException(
                        "Missing election ID in bulk update list",
                        400,
                        "Invalid Input Structure",
                        "One of the update items is missing the required 'election_id' key.",
                        "/elections"
                    );
                }

                if (count($filterData) <= 1) {
                    DB::rollBack();
                    throw new AppException(
                        "No valid update data provided for election $electionId",
                        400,
                        "No Update Data",
                        "The item for election ID $electionId contains no valid fields to update.",
                        "/elections"
                    );
                }

                try {
                    $schoolElection = Elections::findOrFail($electionId);
                    $schoolElection->update($filterData);
                    $result[] = $schoolElection;
                    AdminActionEvent::dispatch(
                        [
                            "permissions" =>  ["schoolAdmin.election.update"],
                            "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                            "schoolBranch" =>  $currentSchool->id,
                            "feature" => "electionManagement",
                            "authAdmin" => $authAdmin,
                            "data" => $result,
                            "message" => "Elections Updated",
                        ]
                    );
                } catch (ModelNotFoundException $e) {
                    DB::rollBack();
                    throw new AppException(
                        "Election not found for update",
                        404,
                        "Election Missing in Bulk",
                        "The election with ID $electionId could not be found, halting bulk update.",
                        "/elections"
                    );
                }
            }

            DB::commit();
            return $result;
        } catch (Throwable $e) {
            DB::rollBack();

            if ($e instanceof AppException) {
                throw $e;
            }

            throw new AppException(
                "Bulk update failed due to a system error",
                500,
                "System Error",
                "An unexpected error occurred during the bulk update process.",
                "/elections"
            );
        }
    }
    public function getUpcomingEligibleElectionsForStudent($currentSchool, $studentId)
    {
        $student = Student::where('school_branch_id', $currentSchool->id)
            ->find($studentId);

        if (is_null($student)) {
            throw new AppException(
                "Student not found",
                404,
                "Student Missing",
                "The student with ID $studentId could not be found in this school branch.",
                "/dashboard"
            );
        }

        $upcomingElections = Elections::where('school_branch_id', $currentSchool->id)
            ->where('voting_start', '<=', now())
            ->where('voting_end', '>=', now())
            ->where('status', 'pending')
            ->with(['electionType'])
            ->get();

        if ($upcomingElections->isEmpty()) {
            return collect();
        }

        $eligibleElections = $upcomingElections->filter(function ($election) use ($student, $currentSchool) {
            $isParticipantEligible = ElectionParticipants::where('election_id', $election->id)
                ->where('school_branch_id', $currentSchool->id)
                ->where('specialty_id', $student->specialty_id)
                ->where('level_id', $student->level_id)
                ->exists();

            return $isParticipantEligible;
        });

        if ($eligibleElections->isEmpty()) {
            throw new AppException(
                "No upcoming eligible elections found",
                404,
                "No Elections Available",
                "There are no active elections that the student is eligible to vote in at this time.",
                "/elections"
            );
        }

        return $eligibleElections;
    }
    public function getPastElection($currentSchool)
    {
        $pastElections = Elections::where("school_branch_id", $currentSchool->id)
            ->where("status", "finished")
            ->with(['electionType'])
            ->get();

        if ($pastElections->isEmpty()) {
            throw new AppException(
                "No past elections found",
                404,
                "Past Elections Missing",
                "There are no elections marked as 'finished' for this school branch.",
                "/elections/past"
            );
        }

        return $pastElections;
    }
    public function addAllowedElectionParticipants(array $electionParticipantsList, $currentSchool, $authAdmin)
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
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.election.add.participants"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "electionManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $result,
                    "message" => "Election Participants Added",
                ]
            );
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
    public function addAllowedParticipantsByOtherElection($currentSchool, $electionId, $targetElectionId, $authAdmin)
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
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.election.add.participants"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "electionManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $result,
                    "message" => "Election Participants Added",
                ]
            );
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function upcomingElectionByStudent($currentSchool, $student)
    {
        $now = Carbon::now();
        $elections = ElectionParticipants::where('school_branch_id', $currentSchool->id)
            ->where('specialty_id', $student->specialty_id)
            ->where('level_id', $student->level_id)
            ->with(['election.electionType'])
            ->get()
            ->pluck('election')
            ->filter(function ($election) use ($now) {
                return $election->application_end >= $now || $election->voting_end >= $now;
            })
            ->unique('id')
            ->values();

        $formatted = $elections->map(function ($election) use ($now) {
            $applicationOpen = $now->between($election->application_start, $election->application_end);
            $votingOpen      = $now->between($election->voting_start, $election->voting_end);

            if (!$applicationOpen && !$votingOpen) {
                return null;
            }

            return [
                "election_name"          => $election->electionType?->election_title ?? "Untitled Election",
                "application_start_date"   => $election->application_start?->format('Y-m-d'),
                "application_end_date"   => $election->application_end?->format('Y-m-d'),
                "election_id"            => $election->id,
                "description"            => $election->electionType?->description ?? "No description available",
                "vote"                   => $votingOpen,
                "application"            => $applicationOpen
            ];
        })->filter()->values();

        return $formatted;
    }
}
