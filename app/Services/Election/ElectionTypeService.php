<?php

namespace App\Services\Election;

use App\Models\ElectionType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;
use App\Exceptions\AppException;
use App\Models\Elections;
use Illuminate\Support\Facades\DB;

class ElectionTypeService
{
    public function createElectionType($electionTypeData, $currentSchool)
    {
        $existingType = ElectionType::where("school_branch_id", $currentSchool->id)
            ->where("election_title", $electionTypeData['election_title'])
            ->exists();

        if ($existingType) {
            throw new AppException(
                "Election type already exists",
                409,
                "Duplicate Election Type",
                "An election type with the title '{$electionTypeData['election_title']}' already exists for this school branch.",
                "/election-types/create"
            );
        }
        try {
            $electionType = ElectionType::create([
                'election_title' => $electionTypeData['election_title'],
                'description' => $electionTypeData['description'] ?? null,
                'school_branch_id' => $currentSchool->id
            ]);
            return $electionType;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to create election type",
                500,
                "Creation Error",
                "An unexpected error occurred while attempting to save the new election type.",
                "/election-types/create"
            );
        }
    }
    public function UpdateElectionType($updateData, $electionTypeId)
    {
        try {
            $electionType = ElectionType::findOrFail($electionTypeId);
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Election type not found for update",
                404,
                "Election Type Missing",
                "The election type with ID $electionTypeId could not be found.",
                "/election-types"
            );
        }

        $cleanedData = array_filter($updateData);

        if (empty($cleanedData)) {
            throw new AppException(
                "No valid data provided for update",
                400,
                "Invalid Update Data",
                "The request contained no valid, non-empty fields to update the election type.",
                "/election-types/" . $electionTypeId . "/edit"
            );
        }

        try {
            if (isset($cleanedData['election_title'])) {
                $existingType = ElectionType::where('election_title', $cleanedData['election_title'])
                    ->where('id', '!=', $electionTypeId)
                    ->where('school_branch_id', $electionType->school_branch_id)
                    ->exists();

                if ($existingType) {
                    throw new AppException(
                        "Election title already exists",
                        409,
                        "Duplicate Title",
                        "An election type with the title '{$cleanedData['election_title']}' already exists.",
                        "/election-types/" . $electionTypeId . "/edit"
                    );
                }
            }

            $electionType->update($cleanedData);
            return $electionType;
        } catch (Throwable $e) {
            if ($e instanceof AppException) {
                throw $e;
            }
            throw new AppException(
                "Failed to update election type",
                500,
                "Update Error",
                "An unexpected error occurred while attempting to save the changes to the election type.",
                "/election-types/" . $electionTypeId . "/edit"
            );
        }
    }
    public function getElectionType($currentSchool)
    {
        $electionTypes = ElectionType::where("school_branch_id", $currentSchool->id)->get();

        if ($electionTypes->isEmpty()) {
            throw new AppException(
                "No election types found",
                404,
                "Election Types Missing",
                "There are no election types defined for this school branch.",
                "/election-types"
            );
        }

        return $electionTypes;
    }
    public function deleteElectionType($electionTypeId, $currentSchool)
    {
        try {
            $electionType = ElectionType::where("school_branch_id", $currentSchool->id)->findOrFail($electionTypeId);
            if (Elections::where("school_branch_id", $currentSchool->id)->where('election_type_id', $electionTypeId)->exists()) {
                throw new AppException(
                    "Cannot delete election type",
                    409,
                    "Type In Use",
                    "This election type is currently associated with one or more elections and cannot be deleted.",
                    "/election-types"
                );
            }

            $electionType->delete();
            return $electionType;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Election type not found for deletion",
                404,
                "Election Type Missing",
                "The election type with ID $electionTypeId could not be found.",
                "/election-types"
            );
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to delete election type",
                500,
                "Deletion Error",
                "An unexpected error occurred while attempting to delete the election type. It may be linked to other records.",
                "/election-types"
            );
        }
    }
    public function getActiveElectionType($currentSchool)
    {
        $electionTypes = ElectionType::where("school_branch_id", $currentSchool->id)
            ->where("status", "active")->get();

        if ($electionTypes->isEmpty()) {
            throw new AppException(
                "No active election types found",
                404,
                "Active Election Types Missing",
                "There are no active election types defined for this school branch.",
                "/election-types"
            );
        }

        return $electionTypes;
    }
    public function deactivateElectionType($currentSchool, $electionTypeId)
    {
        try {
            $electionType = ElectionType::where("school_branch_id", $currentSchool->id)
                ->findOrFail($electionTypeId);

            if ($electionType->status === 'inactive') {
                throw new AppException(
                    "Election type is already inactive",
                    409,
                    "Already Inactive",
                    "The election type with ID $electionTypeId is already set to 'inactive'.",
                    "/election-types/" . $electionTypeId
                );
            }

            $electionType->status = 'inactive';
            $electionType->save();

            return $electionType;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Election type not found for deactivation",
                404,
                "Election Type Missing",
                "The election type with ID $electionTypeId could not be found.",
                "/election-types"
            );
        } catch (Throwable $e) {
            if ($e instanceof AppException) {
                throw $e;
            }
            throw new AppException(
                "Failed to deactivate election type",
                500,
                "Deactivation Error",
                "An unexpected error occurred while attempting to set the election type status to inactive.",
                "/election-types/" . $electionTypeId
            );
        }
    }
    public function bulkDeactivateElectionType($currentSchool, $electionTypeIds)
    {
        $result = [];
        try {
            DB::beginTransaction();

            foreach ($electionTypeIds as $electionTypeId) {
                try {
                    $electionType = ElectionType::where("school_branch_id", $currentSchool->id)
                        ->findOrFail($electionTypeId);

                    $electionType->status = 'inactive';
                    $electionType->save();
                    $result[] = $electionType;
                } catch (ModelNotFoundException $e) {
                    DB::rollBack();
                    throw new AppException(
                        "Election type not found for deactivation",
                        404,
                        "Election Type Missing in Bulk",
                        "The election type with ID $electionTypeId could not be found, halting bulk deactivation.",
                        "/election-types"
                    );
                }
            }

            if (empty($result) && !empty($electionTypeIds)) {
                DB::rollBack();
                throw new AppException(
                    "Bulk deactivation failed to process any valid ID",
                    400,
                    "Processing Error",
                    "The provided list of IDs resulted in no successful deactivations.",
                    "/election-types"
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
                "/election-types"
            );
        }
    }
    public function bulkActivateElectionType($currentSchool, $electionTypeIds)
    {
        $result = [];
        try {
            DB::beginTransaction();

            foreach ($electionTypeIds as $electionTypeId) {
                try {
                    $electionType = ElectionType::where("school_branch_id", $currentSchool->id)
                        ->findOrFail($electionTypeId['election_type_id']);

                    $electionType->status = 'active';
                    $electionType->save();
                    $result[] = $electionType;
                } catch (ModelNotFoundException $e) {
                    DB::rollBack();
                    throw new AppException(
                        "Election type not found for activation",
                        404,
                        "Election Type Missing in Bulk",
                        "The election type with ID {$electionTypeId['election_type_id']} could not be found, halting bulk activation.",
                        "/election-types"
                    );
                }
            }

            if (empty($result) && !empty($electionTypeIds)) {
                DB::rollBack();
                throw new AppException(
                    "Bulk activation failed to process any valid ID",
                    400,
                    "Processing Error",
                    "The provided list of IDs resulted in no successful activations.",
                    "/election-types"
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
                "/election-types"
            );
        }
    }
    public function activateElectionType($electionTypeId, $currentSchool)
    {
        try {
            $electionType = ElectionType::where("school_branch_id", $currentSchool->id)
                ->findOrFail($electionTypeId);

            if ($electionType->status === 'active') {
                throw new AppException(
                    "Election type is already active",
                    409,
                    "Already Active",
                    "The election type with ID $electionTypeId is already set to 'active'.",
                    "/election-types/" . $electionTypeId
                );
            }

            $electionType->status = 'active';
            $electionType->save();

            return $electionType;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Election type not found for activation",
                404,
                "Election Type Missing",
                "The election type with ID $electionTypeId could not be found.",
                "/election-types"
            );
        } catch (Throwable $e) {
            if ($e instanceof AppException) {
                throw $e;
            }
            throw new AppException(
                "Failed to activate election type",
                500,
                "Activation Error",
                "An unexpected error occurred while attempting to set the election type status to active.",
                "/election-types/" . $electionTypeId
            );
        }
    }

    public function getElectionTypeDetails($electionTypeId, $currentSchool)
    {
        try {
            $electionTypeDetails = ElectionType::where("school_branch_id", $currentSchool->id)
                ->findOrFail($electionTypeId);

            return $electionTypeDetails;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Election type details not found",
                404,
                "Election Type Missing",
                "The election type with ID $electionTypeId could not be found for this school branch.",
                "/election-types"
            );
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to retrieve election type details",
                500,
                "Retrieval Error",
                "An unexpected error occurred while fetching the election type.",
                "/election-types"
            );
        }
    }
}
