<?php

namespace App\Services\Election;

use App\Models\ElectionRoles;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;
use App\Exceptions\AppException;
use App\Models\Elections;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\ElectionParticipants;
class ElectionRoleService
{
    public function createElectionRole(array $data, $currentSchool)
    {
        try {
            DB::beginTransaction();

            $existingElectionRole = ElectionRoles::where("school_branch_id", $currentSchool->id)
                ->where("name", $data["name"])
                ->exists();

            if ($existingElectionRole) {
                DB::rollBack();
                throw new AppException(
                    "Election role already exists",
                    409,
                    "Duplicate Role Name",
                    "An election role with the name '{$data['name']}' already exists for this school branch.",
                    "/election-roles/create"
                );
            }

            $electionRole = new ElectionRoles();
            $electionRole->name = $data["name"];
            $electionRole->description = $data["description"];
            $electionRole->election_type_id = $data["election_type_id"];
            $electionRole->school_branch_id = $currentSchool->id;
            $electionRole->save();

            Role::create([
                'uuid' => Str::uuid(),
                'name' => $data["name"],
                'guard_name' => 'student',
                'school_branch_id' => $currentSchool->id,
            ]);

            DB::commit();
            return $electionRole;
        } catch (Throwable $e) {
            DB::rollBack();

            if ($e instanceof AppException) {
                throw $e;
            }

            throw new AppException(
                "Failed to create election role",
                500,
                "Creation Error",
                "An unexpected error occurred while attempting to save the new election role and associated student role.",
                "/election-roles/create"
            );
        }
    }
    public function updateElectionRole(array $data, $currentSchool, $electionRoleId)
    {
        try {
            DB::beginTransaction();

            $electionRole = ElectionRoles::where("school_branch_id", $currentSchool->id)->find($electionRoleId);

            if (is_null($electionRole)) {
                DB::rollBack();
                throw new AppException(
                    "Election Role not found for update",
                    404,
                    "Role Missing",
                    "The election role with ID $electionRoleId could not be found in this school branch.",
                    "/election-roles"
                );
            }

            $filteredData = array_filter($data);

            if (empty($filteredData)) {
                DB::rollBack();
                throw new AppException(
                    "No valid data provided for update",
                    400,
                    "Invalid Update Data",
                    "The request contained no valid, non-empty fields to update the election role.",
                    "/election-roles/" . $electionRoleId . "/edit"
                );
            }

            $newName = $filteredData['name'] ?? null;
            if ($newName) {
                $existingRole = ElectionRoles::where("school_branch_id", $currentSchool->id)
                    ->where("name", $newName)
                    ->where("id", "!=", $electionRoleId)
                    ->exists();

                if ($existingRole) {
                    DB::rollBack();
                    throw new AppException(
                        "Role name already exists",
                        409,
                        "Duplicate Role Name",
                        "An election role with the name '{$newName}' already exists.",
                        "/election-roles/" . $electionRoleId . "/edit"
                    );
                }
            }

            $oldName = $electionRole->name;
            $electionRole->update($filteredData);

            if ($newName && $newName !== $oldName) {
                $role = Role::where('name', $oldName)
                    ->where('school_branch_id', $currentSchool->id)
                    ->where('guard_name', 'student')
                    ->first();

                if ($role) {
                    $role->name = $newName;
                    $role->save();
                } else {
                    Role::create([
                        'uuid' => Str::uuid(),
                        'name' => $newName,
                        'guard_name' => 'student',
                        'school_branch_id' => $currentSchool->id,
                    ]);
                }
            }

            DB::commit();
            return $electionRole;
        } catch (Throwable $e) {
            DB::rollBack();

            if ($e instanceof AppException) {
                throw $e;
            }
            throw new AppException(
                "Failed to update election role",
                500,
                "Update Error",
                "An unexpected error occurred while attempting to save the changes to the election role and its associated student role.",
                "/election-roles/" . $electionRoleId . "/edit"
            );
        }
    }
    public function bulkUpdateElectionRole(array $UpdateElectionList)
    {
        $result = [];
        try {
            DB::beginTransaction();

            foreach ($UpdateElectionList as $UpdateElection) {
                $electionRoleId = $UpdateElection['election_role_id'] ?? null;

                if (is_null($electionRoleId)) {
                    DB::rollBack();
                    throw new AppException(
                        "Missing election role ID in bulk update list",
                        400,
                        "Invalid Input Structure",
                        "One of the update items is missing the required 'election_role_id' key.",
                        "/election-roles"
                    );
                }

                $filteredData = array_filter($UpdateElection);

                if (count($filteredData) <= 1) {
                    DB::rollBack();
                    throw new AppException(
                        "No valid update data provided for election role $electionRoleId",
                        400,
                        "No Update Data",
                        "The item for election role ID $electionRoleId contains no valid fields to update.",
                        "/election-roles"
                    );
                }

                try {
                    $electionRole = ElectionRoles::findOrFail($electionRoleId);
                    $newName = $filteredData['name'] ?? null;
                    if ($newName) {
                        $existingRole = ElectionRoles::where("school_branch_id", $electionRole->school_branch_id)
                            ->where("name", $newName)
                            ->where("id", "!=", $electionRoleId)
                            ->exists();

                        if ($existingRole) {
                            DB::rollBack();
                            throw new AppException(
                                "Role name already exists",
                                409,
                                "Duplicate Role Name",
                                "An election role with the name '{$newName}' already exists.",
                                "/election-roles"
                            );
                        }
                    }

                    $electionRole->update($filteredData);
                    $result[] = $electionRole;
                } catch (ModelNotFoundException $e) {
                    DB::rollBack();
                    throw new AppException(
                        "Election role not found for update",
                        404,
                        "Role Missing in Bulk",
                        "The election role with ID $electionRoleId could not be found, halting bulk update.",
                        "/election-roles"
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
                "/election-roles"
            );
        }
    }
    public function deleteElectionRole($electionRoleId, $currentSchool)
    {
        try {
            DB::beginTransaction();

            $electionRole = ElectionRoles::where("school_branch_id", $currentSchool->id)->find($electionRoleId);

            if (is_null($electionRole)) {
                DB::rollBack();
                throw new AppException(
                    "Election Role not found for deletion",
                    404,
                    "Role Missing",
                    "The election role with ID $electionRoleId could not be found in this school branch.",
                    "/election-roles"
                );
            }

            $roleName = $electionRole->name;
            $electionRole->delete();

            $role = Role::where('name', $roleName)
                ->where('school_branch_id', $currentSchool->id)
                ->first();

            if ($role) {
                $role->delete();
            }

            DB::commit();
            return $electionRole;
        } catch (Throwable $e) {
            DB::rollBack();

            if ($e instanceof AppException) {
                throw $e;
            }
            throw new AppException(
                "Failed to delete election role",
                500,
                "Deletion Error",
                "An unexpected error occurred while attempting to delete the election role and its associated student role. It may be linked to other active records (e.g., candidates).",
                "/election-roles"
            );
        }
    }
    public function bulkDeleteElectionRole($electionRoleIds)
    {
        $result = [];
        try {
            DB::beginTransaction();

            foreach ($electionRoleIds as $electionRoleItem) {
                $electionRoleId = $electionRoleItem['election_role_id'] ?? null;

                if (is_null($electionRoleId)) {
                    DB::rollBack();
                    throw new AppException(
                        "Missing election role ID in bulk deletion list",
                        400,
                        "Invalid Input Structure",
                        "One of the deletion items is missing the required 'election_role_id' key.",
                        "/election-roles"
                    );
                }

                try {
                    $electionRole = ElectionRoles::findOrFail($electionRoleId);

                    $roleName = $electionRole->name;
                    $schoolBranchId = $electionRole->school_branch_id;
                    $electionRole->delete();

                    $role = Role::where('name', $roleName)
                        ->where('school_branch_id', $schoolBranchId)
                        ->first();

                    if ($role) {
                        $role->delete();
                    }

                    $result[] = $electionRole;
                } catch (ModelNotFoundException $e) {
                    DB::rollBack();
                    throw new AppException(
                        "Election role not found for deletion",
                        404,
                        "Role Missing in Bulk",
                        "The election role with ID $electionRoleId could not be found, halting bulk deletion.",
                        "/election-roles"
                    );
                }
            }

            if (empty($result) && !empty($electionRoleIds)) {
                DB::rollBack();
                throw new AppException(
                    "Bulk deletion failed to process any valid ID",
                    400,
                    "Processing Error",
                    "The provided list of IDs resulted in no successful deletions.",
                    "/election-roles"
                );
            }

            DB::commit();
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
                "An unexpected error occurred during the bulk deletion process. Check for associated candidates or elections.",
                "/election-roles"
            );
        }
    }
    public function getAllElectionRoles($currentSchool)
    {
        $electionRoles = ElectionRoles::where("school_branch_id", $currentSchool->id)
            ->with(['electionType'])
            ->get();

        if ($electionRoles->isEmpty()) {
            throw new AppException(
                "No election roles found",
                404,
                "Election Roles Missing",
                "There are no election roles defined for this school branch.",
                "/election-roles"
            );
        }

        return $electionRoles;
    }

        public function getElectionRoles($currentSchool, $electionId)
    {
        $election = Elections::findOrFail($electionId);
        $electionRoles = ElectionRoles::where('school_branch_id', $currentSchool->id)
            ->where('election_type_id', $election->election_type_id)
            ->with(['electionType'])
            ->get();
        return $electionRoles;
    }
    public function getElectionRolesByElection($currentSchool, $electionId)
    {
        try {
            $election = Elections::where("school_branch_id", $currentSchool->id)
                ->findOrFail($electionId);
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Election not found",
                404,
                "Election Missing",
                "The election with ID $electionId could not be found to retrieve its roles.",
                "/elections"
            );
        }

        $electionRoles = ElectionRoles::where('school_branch_id', $currentSchool->id)
            ->where('election_type_id', $election->election_type_id)
            ->where("status", "active")
            ->with(['electionType'])
            ->get();

        if ($electionRoles->isEmpty()) {
            throw new AppException(
                "No active election roles found for this election type",
                404,
                "Election Roles Missing",
                "There are no active roles defined for the election type associated with election ID $electionId.",
                "/elections/" . $electionId . "/roles"
            );
        }

        return $electionRoles;
    }
    public function activateRole($electionRoleId)
    {
        try {
            $electionRole = ElectionRoles::findOrFail($electionRoleId);

            if ($electionRole->status === 'active') {
                throw new AppException(
                    "Election role is already active",
                    409,
                    "Already Active",
                    "The election role with ID $electionRoleId is already set to 'active'.",
                    "/election-roles/" . $electionRoleId
                );
            }

            $electionRole->status = 'active';
            $electionRole->save();

            return $electionRole;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Election role not found for activation",
                404,
                "Role Missing",
                "The election role with ID $electionRoleId could not be found.",
                "/election-roles"
            );
        } catch (Throwable $e) {
            if ($e instanceof AppException) {
                throw $e;
            }
            throw new AppException(
                "Failed to activate election role",
                500,
                "Activation Error",
                "An unexpected error occurred while attempting to set the election role status to active.",
                "/election-roles/" . $electionRoleId
            );
        }
    }
    public function bulkActivateElectionRole($electionRoleIds)
    {
        $result = [];
        try {
            DB::beginTransaction();

            foreach ($electionRoleIds as $electionRoleItem) {
                $electionRoleId = $electionRoleItem['election_role_id'] ?? null;

                if (is_null($electionRoleId)) {
                    DB::rollBack();
                    throw new AppException(
                        "Missing election role ID in bulk activation list",
                        400,
                        "Invalid Input Structure",
                        "One of the activation items is missing the required 'election_role_id' key.",
                        "/election-roles"
                    );
                }

                try {
                    $electionRole = ElectionRoles::findOrFail($electionRoleId);

                    $electionRole->status = 'active';
                    $electionRole->save();
                    $result[] = $electionRole;
                } catch (ModelNotFoundException $e) {
                    DB::rollBack();
                    throw new AppException(
                        "Election role not found for activation",
                        404,
                        "Role Missing in Bulk",
                        "The election role with ID $electionRoleId could not be found, halting bulk activation.",
                        "/election-roles"
                    );
                }
            }

            if (empty($result) && !empty($electionRoleIds)) {
                DB::rollBack();
                throw new AppException(
                    "Bulk activation failed to process any valid ID",
                    400,
                    "Processing Error",
                    "The provided list of IDs resulted in no successful activations.",
                    "/election-roles"
                );
            }

            DB::commit();
            return $result;
        } catch (Throwable $e) {
            DB::rollBack();

            if ($e instanceof AppException) {
                throw $e;
            }

            throw new AppException(
                "Bulk activation failed due to a system error",
                500,
                "System Error",
                "An unexpected error occurred during the bulk activation process.",
                "/election-roles"
            );
        }
    }
    public function getActiveRoles($currentSchool, $electionId)
    {
        try {
            $election = Elections::findOrFail($electionId);
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Election not found",
                404,
                "Election Missing",
                "The election with ID $electionId could not be found to retrieve its roles.",
                "/elections"
            );
        }

        $activeRoles = ElectionRoles::where("school_branch_id", $currentSchool->id)
            ->where("election_type_id", $election->election_type_id)
            ->get();

        if ($activeRoles->isEmpty()) {
            throw new AppException(
                "No roles found for this election type",
                404,
                "Election Roles Missing",
                "There are no election roles defined for the type associated with election ID $electionId in this school branch.",
                "/elections/" . $electionId . "/roles"
            );
        }

        return $activeRoles;
    }
    public function deactivateRole($electionRoleId)
    {
        try {
            $electionRole = ElectionRoles::findOrFail($electionRoleId);

            if ($electionRole->status === 'inactive') {
                throw new AppException(
                    "Election role is already inactive",
                    409,
                    "Already Inactive",
                    "The election role with ID $electionRoleId is already set to 'inactive'.",
                    "/election-roles/" . $electionRoleId
                );
            }

            $electionRole->status = 'inactive';
            $electionRole->save();

            return $electionRole;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Election role not found for deactivation",
                404,
                "Role Missing",
                "The election role with ID $electionRoleId could not be found.",
                "/election-roles"
            );
        } catch (Throwable $e) {
            if ($e instanceof AppException) {
                throw $e;
            }
            throw new AppException(
                "Failed to deactivate election role",
                500,
                "Deactivation Error",
                "An unexpected error occurred while attempting to set the election role status to inactive.",
                "/election-roles/" . $electionRoleId
            );
        }
    }
    public function bulkDeactivateRole($electionRoleIds)
    {
        $result = [];
        try {
            DB::beginTransaction();

            foreach ($electionRoleIds as $electionRoleItem) {
                $electionRoleId = $electionRoleItem['election_role_id'] ?? null;

                if (is_null($electionRoleId)) {
                    DB::rollBack();
                    throw new AppException(
                        "Missing election role ID in bulk deactivation list",
                        400,
                        "Invalid Input Structure",
                        "One of the deactivation items is missing the required 'election_role_id' key.",
                        "/election-roles"
                    );
                }

                try {
                    $electionRole = ElectionRoles::findOrFail($electionRoleId);

                    if ($electionRole->status === 'inactive') {
                    } else {
                        $electionRole->status = 'inactive';
                        $electionRole->save();
                    }

                    $result[] = $electionRole;
                } catch (ModelNotFoundException $e) {
                    DB::rollBack();
                    throw new AppException(
                        "Election role not found for deactivation",
                        404,
                        "Role Missing in Bulk",
                        "The election role with ID $electionRoleId could not be found, halting bulk deactivation.",
                        "/election-roles"
                    );
                }
            }

            if (empty($result) && !empty($electionRoleIds)) {
                DB::rollBack();
                throw new AppException(
                    "Bulk deactivation failed to process any valid ID",
                    400,
                    "Processing Error",
                    "The provided list of IDs resulted in no successful deactivations.",
                    "/election-roles"
                );
            }

            DB::commit();
            return $result;
        } catch (Throwable $e) {
            DB::rollBack();

            if ($e instanceof AppException) {
                throw $e;
            }

            throw new AppException(
                "Bulk deactivation failed due to a system error",
                500,
                "System Error",
                "An unexpected error occurred during the bulk deactivation process.",
                "/election-roles"
            );
        }
    }
    public function getElectionRoleDetails($electionRoleId, $currentSchool)
{
    try {
        $electionRoleDetails = ElectionRoles::where("school_branch_id", $currentSchool->id)
            ->with(['electionType'])
            ->findOrFail($electionRoleId);

        return $electionRoleDetails;
    } catch (ModelNotFoundException $e) {
        throw new AppException(
            "Election role details not found",
            404,
            "Role Missing",
            "The election role with ID $electionRoleId could not be found for this school branch.",
            "/election-roles"
        );
    } catch (Throwable $e) {
        throw new AppException(
            "Failed to retrieve election role details",
            500,
            "Retrieval Error",
            "An unexpected error occurred while fetching the election role.",
            "/election-roles"
        );
    }
}

    public function getStudentElectionRoles($currentSchool, $student, $electionId)
    {
        $now = Carbon::now();
        $schoolBranchId = $currentSchool->id;

        $participant = ElectionParticipants::where('school_branch_id', $schoolBranchId)
            ->where('specialty_id', $student->specialty_id)
            ->where('level_id', $student->level_id)
            ->where('election_id', $electionId)
            ->with([
                'election.electionType',
                'election.electionRoles' => function ($q) {
                    $q->orderBy('order');
                }
            ])
            ->first();

        if (!$participant || !$participant->election) {
            throw new AppException(
                "Not Eligible",
                403,
                "You are not eligible for this election",
                "Your specialty or level is not part of this election."
            );
        }

        $election = $participant->election;
        $electionType = $election->electionType;

        $applicationOpen = $now->between($election->application_start, $election->application_end);
        $votingOpen      = $now->between($election->voting_start, $election->voting_end);

        if (!$applicationOpen && !$votingOpen) {
            throw new AppException(
                "Election Closed",
                410,
                "This election is no longer active",
                "Application and voting periods have ended."
            );
        }

        $roles = $election->electionRoles->map(function ($role) {
            return [
                'role_id'          => $role->id,
                'role_title'       => $role->title,
                'max_candidates'   => $role->max_candidates ?? 1,
                'description'      => $role->description ?? null,
            ];
        })->values();

        return [
            'election_id'         => $election->id,
            'election_name'       => $electionType?->election_title ?? 'Untitled Election',
            'description'         => $electionType?->description ?? 'No description available',
            'application_start'   => $election->application_start,
            'application_end'     => $election->application_end,
            'voting_start'        => $election->voting_start,
            'voting_end'          => $election->voting_end,
            'is_application_open' => $applicationOpen,
            'is_voting_open'      => $votingOpen,
            'roles'               => $roles,
        ];
    }
}
