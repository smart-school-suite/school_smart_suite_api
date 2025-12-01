<?php

namespace App\Services\Election;

use App\Models\ElectionCandidates;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;
use App\Exceptions\AppException;
use Illuminate\Support\Facades\DB;
use App\Events\Actions\AdminActionEvent;

class ElectionCandidateService
{
    public function getElectionCandidatesByElection(string $electionId, $currentSchool)
    {
        $getElectionCandidates = ElectionCandidates::where("school_branch_id", $currentSchool->id)
            ->where("election_id", $electionId)
            ->with(['student', 'electionApplication'])
            ->get();

        if ($getElectionCandidates->isEmpty()) {
            throw new AppException(
                "No candidates found for election ID $electionId",
                404,
                "Candidates Missing",
                "There are no candidates registered for the specified election in this school branch.",
                "/elections/$electionId/candidates"
            );
        }

        return $getElectionCandidates;
    }
    public function getElectionCandidates($currentSchool)
    {
        $getElectionCandidates = ElectionCandidates::where("school_branch_id", $currentSchool->id)
            ->with(['student.specialty.level', 'electionApplication', 'electionRole.electionType'])
            ->get();

        if ($getElectionCandidates->isEmpty()) {
            throw new AppException(
                "No election candidates found",
                404,
                "Candidates Missing",
                "There are no election candidates available for this school branch.",
                "/elections/candidates"
            );
        }

        return $getElectionCandidates;
    }
    public function getElectionCandidateDetails($currentSchool, $candidateId)
    {
        try {
            $candidate = ElectionCandidates::where("school_branch_id", $currentSchool->id)
                ->with(['student', 'electionRole', 'electionApplication.election'])
                ->findOrFail($candidateId);

            return $candidate;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Candidate not found",
                404,
                "Candidate Details Missing",
                "The election candidate with ID $candidateId could not be found in this school branch.",
                "/elections/candidates"
            );
        }
    }
    public function disqualifyCandidate($currentSchool, $candidateId, $authAdmin)
    {
        try {
            $candidate = ElectionCandidates::where("school_branch_id", $currentSchool->id)
                ->findOrFail($candidateId);

            $candidate->isActive = false;
            $candidate->save();

            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.electionCandidate.disqualify"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "electionRoleManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $candidate,
                    "message" => "Election Candidate Disqualified",
                ]
            );
            return $candidate;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Candidate not found for disqualification",
                404,
                "Candidate Missing",
                "The election candidate with ID $candidateId could not be found in this school branch.",
                "/elections/candidates"
            );
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to disqualify candidate",
                500,
                "Disqualification Error",
                "An unexpected error occurred while attempting to disqualify the candidate.",
                "/elections/candidates/" . $candidateId
            );
        }
    }
    public function reinstateCandidate($currentSchool, $candidateId, $authAdmin)
    {
        try {
            $candidate = ElectionCandidates::where("school_branch_id", $currentSchool->id)
                ->findOrFail($candidateId);

            $candidate->isActive = true;
            $candidate->save();

            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.electionCandidate.reinstate"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "electionRoleManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $candidate,
                    "message" => "Election Candidate Reinstated",
                ]
            );
            return $candidate;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Candidate not found for reinstatement",
                404,
                "Candidate Missing",
                "The election candidate with ID $candidateId could not be found in this school branch.",
                "/elections/candidates"
            );
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to reinstate candidate",
                500,
                "Reinstatement Error",
                "An unexpected error occurred while attempting to reinstate the candidate.",
                "/elections/candidates/" . $candidateId
            );
        }
    }
    public function bulkDisqualifyCandidate($currentSchool, $candidateIds, $authAdmin)
    {
        $result = [];
        try {

            DB::beginTransaction();

            foreach ($candidateIds as $candidateItem) {
                $candidateId = $candidateItem['candidate_id'] ?? null;

                if (is_null($candidateId)) {
                    DB::rollBack();
                    throw new AppException(
                        "Missing candidate ID in bulk disqualification list",
                        400,
                        "Invalid Input Structure",
                        "One of the items is missing the required 'candidate_id' key.",
                        "/elections/candidates"
                    );
                }

                try {
                    $candidate = ElectionCandidates::where("school_branch_id", $currentSchool->id)
                        ->findOrFail($candidateId);

                    $candidate->isActive = false;
                    $candidate->save();

                    $result[] = $candidate;
                } catch (ModelNotFoundException $e) {
                    DB::rollBack();
                    throw new AppException(
                        "Candidate not found for disqualification",
                        404,
                        "Candidate Missing in Bulk",
                        "The election candidate with ID $candidateId could not be found in this school branch, halting bulk process.",
                        "/elections/candidates"
                    );
                }
            }

            if (empty($result) && !empty($candidateIds)) {
                DB::rollBack();
                throw new AppException(
                    "Bulk disqualification failed to process any valid ID",
                    400,
                    "Processing Error",
                    "The provided list of IDs resulted in no successful disqualifications.",
                    "/elections/candidates"
                );
            }

            DB::commit();
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.electionCandidate.disqualify"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "electionRoleManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $result,
                    "message" => "Election Candidates Disqualified",
                ]
            );
            return $result;
        } catch (Throwable $e) {
            DB::rollBack();

            if ($e instanceof AppException) {
                throw $e;
            }
            throw new AppException(
                "Bulk disqualification failed due to a system error",
                500,
                "System Error",
                "An unexpected error occurred during the bulk disqualification process.",
                "/elections/candidates"
            );
        }
    }
    public function bulkReinstateCandidate($currentSchool, $candidateIds, $authAdmin)
    {
        $result = [];
        try {
            DB::beginTransaction();

            foreach ($candidateIds as $candidateItem) {
                $candidateId = $candidateItem['candidate_id'] ?? null;

                if (is_null($candidateId)) {
                    DB::rollBack();
                    throw new AppException(
                        "Missing candidate ID in bulk reinstatement list",
                        400,
                        "Invalid Input Structure",
                        "One of the items is missing the required 'candidate_id' key.",
                        "/elections/candidates"
                    );
                }

                try {
                    $candidate = ElectionCandidates::where("school_branch_id", $currentSchool->id)
                        ->findOrFail($candidateId);

                    $candidate->isActive = true;
                    $candidate->save();

                    $result[] = $candidate;
                } catch (ModelNotFoundException $e) {
                    DB::rollBack();
                    throw new AppException(
                        "Candidate not found for reinstatement",
                        404,
                        "Candidate Missing in Bulk",
                        "The election candidate with ID $candidateId could not be found in this school branch, halting bulk process.",
                        "/elections/candidates"
                    );
                }
            }

            if (empty($result) && !empty($candidateIds)) {
                DB::rollBack();
                throw new AppException(
                    "Bulk reinstatement failed to process any valid ID",
                    400,
                    "Processing Error",
                    "The provided list of IDs resulted in no successful reinstatements.",
                    "/elections/candidates"
                );
            }

            DB::commit();

            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.electionCandidate.reinstate"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "electionRoleManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $candidate,
                    "message" => "Election Candidates Reinstated",
                ]
            );
            return $result;
        } catch (Throwable $e) {
            DB::rollBack();

            if ($e instanceof AppException) {
                throw $e;
            }
            throw new AppException(
                "Bulk reinstatement failed due to a system error",
                500,
                "System Error",
                "An unexpected error occurred during the bulk reinstatement process.",
                "/elections/candidates"
            );
        }
    }
}
