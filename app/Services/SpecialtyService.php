<?php

namespace App\Services;

use App\Exceptions\AppException;
use App\Jobs\NotificationJobs\SendAdminSpecialtyCreatedNotificationJob;
use App\Jobs\StatisticalJobs\OperationalJobs\SpecialtyStatJob;
use App\Models\Specialty;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SpecialtyService
{
    public function createSpecialty(array $data, $currentSchool)
    {
        try {
            $existingSpecialty = Specialty::where("school_branch_id", $currentSchool->id)
                ->where("specialty_name", $data['specialty_name'])
                ->where("level_id", $data['level_id'])
                ->where("department_id", $data['department_id'])
                ->first();

            if ($existingSpecialty) {
                throw new AppException(
                    "A specialty with the same name, level, and department already exists.",
                    409,
                    "Duplicate Specialty",
                    "You are trying to create a specialty that already exists. Please check the details and try again.",
                    null
                );
            }

            $specialty = new Specialty();
            $specialtyId = Str::uuid();
            $specialty->id = $specialtyId;
            $specialty->school_branch_id = $currentSchool->id;
            $specialty->department_id = $data["department_id"];
            $specialty->specialty_name = $data["specialty_name"];
            $specialty->registration_fee = $data["registration_fee"];
            $specialty->school_fee = $data["school_fee"];
            $specialty->description = $data["description"] ?? null;
            $specialty->level_id = $data["level_id"];
            $specialty->save();

            SpecialtyStatJob::dispatch($specialtyId, $currentSchool->id);
            SendAdminSpecialtyCreatedNotificationJob::dispatch($currentSchool->id, $data);

            return $specialty;
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An error occurred while creating the specialty. Please try again.",
                500,
                "Creation Error",
                "We encountered an issue while trying to create the specialty. " . $e->getMessage(),
                null
            );
        }
    }

    public function updateSpecialty(array $data, $currentSchool, $specialtyId)
    {
        try {
            $specialty = Specialty::where("school_branch_id", $currentSchool->id)
                ->find($specialtyId);

            if (!$specialty) {
                throw new AppException(
                    "The specialty you are trying to update was not found.",
                    404,
                    "Specialty Not Found",
                    "We could not find the specialty with the provided ID for this school. Please verify the ID and try again.",
                    null
                );
            }

            $filteredData = array_filter($data);
            if (empty($filteredData)) {
                throw new AppException(
                    "No valid data was provided for the update.",
                    400,
                    "No Data Provided",
                    "The request body did not contain any valid fields to update the specialty.",
                    null
                );
            }

            $specialty->update($filteredData);
            return $specialty;
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while updating the specialty. Please try again later.",
                500,
                "Update Error",
                "We encountered a server-side issue while attempting to update the specialty.",
                null
            );
        }
    }

    public function deleteSpecialty($currentSchool, $specialtyId)
    {
        try {
            $specialty = Specialty::where("school_branch_id", $currentSchool->id)
                ->find($specialtyId);

            if (!$specialty) {
                throw new AppException(
                    "The specialty you are trying to delete was not found.",
                    404,
                    "Specialty Not Found",
                    "We could not find the specialty with the provided ID for this school. It may have already been deleted.",
                    null
                );
            }

            $specialty->delete();
            return $specialty;
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while deleting the specialty. Please try again later.",
                500,
                "Deletion Error",
                "We encountered a server-side issue while attempting to delete the specialty.",
                null
            );
        }
    }

    public function getSpecialties($currentSchool)
    {
        $specialtyData = Specialty::where("school_branch_id", $currentSchool->id)
            ->with(['level', 'hos.hosable'])
            ->get();

        if ($specialtyData->isEmpty()) {
            throw new AppException(
                "There are no specialties available for this school branch yet.",
                404,
                "No Specialties Found",
                "We could not find any specialties associated with your school branch. Please try creating one first.",
                null
            );
        }

        return $specialtyData;
    }

    public function getSpecailtyDetails($currentSchool, $specialtyId)
    {
        $specialty = Specialty::where("school_branch_id", $currentSchool->id)
            ->with(['level', 'department', 'hos.hosable'])
            ->find($specialtyId);

        if (!$specialty) {
            throw new AppException(
                "The specialty you are looking for was not found.",
                404,
                "Specialty Not Found",
                "We could not find the specialty with the provided ID for this school. Please verify the ID and try again.",
                null
            );
        }

        return $specialty;
    }

    public function deactivateSpecialty($specialtyId)
    {
        try {
            $specialty = Specialty::findOrFail($specialtyId);
            if ($specialty->status === "inactive") {
                throw new AppException(
                    "The specialty is already inactive.",
                    409,
                    "Status Conflict",
                    "You cannot deactivate a specialty that is already marked as inactive.",
                    null
                );
            }
            $specialty->status = "inactive";
            $specialty->save();
            return $specialty;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "The specialty you are trying to deactivate was not found.",
                404,
                "Specialty Not Found",
                "We could not find the specialty with the provided ID. Please verify the ID and try again.",
                null
            );
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while deactivating the specialty.",
                500,
                "Deactivation Error",
                "We encountered a server-side issue. Please try again later.",
                null
            );
        }
    }

    public function activateSpecialty($specialtyId)
    {
        try {
            $specialty = Specialty::findOrFail($specialtyId);
            if ($specialty->status === "active") {
                throw new AppException(
                    "The specialty is already active.",
                    409,
                    "Status Conflict",
                    "You cannot activate a specialty that is already marked as active.",
                    null
                );
            }
            $specialty->status = "active";
            $specialty->save();
            return $specialty;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "The specialty you are trying to activate was not found.",
                404,
                "Specialty Not Found",
                "We could not find the specialty with the provided ID. Please verify the ID and try again.",
                null
            );
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while activating the specialty.",
                500,
                "Activation Error",
                "We encountered a server-side issue. Please try again later.",
                null
            );
        }
    }

    public function bulkUpdateSpecialty($updateDataList)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($updateDataList as $updateData) {
                $specialtyId = $updateData['id'] ?? null;
                if (!$specialtyId) {
                    throw new AppException(
                        "A specialty ID is missing from one of the update entries.",
                        400,
                        "Invalid Input",
                        "Each entry in the update list must contain a valid 'id' field.",
                        null
                    );
                }
                $specialty = Specialty::findOrFail($specialtyId);
                $cleanedData = array_filter($updateData, function ($value, $key) {
                    return $key !== 'id' && $value !== null && $value !== '';
                }, ARRAY_FILTER_USE_BOTH);

                if (empty($cleanedData)) {
                    throw new AppException(
                        "No valid data provided for one of the specialties to be updated.",
                        400,
                        "No Data Provided",
                        "The entry for specialty ID '{$specialtyId}' did not contain any valid fields to update.",
                        null
                    );
                }
                $specialty->update($cleanedData);
                $result[] = $specialty;
            }
            DB::commit();
            return $result;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            throw new AppException(
                "One or more specialties you tried to update were not found.",
                404,
                "Specialty Not Found",
                "We could not find a specialty for one of the provided IDs. Please check the list and try again.",
                null
            );
        } catch (AppException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            throw new AppException(
                "An unexpected error occurred while updating specialties in bulk.",
                500,
                "Bulk Update Error",
                "A server-side issue prevented the bulk update from completing successfully.",
                null
            );
        }
    }

    public function bulkDeactivateSpecialty($specialtyIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($specialtyIds as $specialtyIdData) {
                $specialtyId = $specialtyIdData['specialty_id'] ?? null;
                if (!$specialtyId) {
                    throw new AppException(
                        "An invalid specialty ID was provided in the list.",
                        400,
                        "Invalid Input",
                        "Each entry must contain a valid 'specialty_id' field.",
                        null
                    );
                }
                $specialty = Specialty::findOrFail($specialtyId);
                if ($specialty->status === "inactive") {
                    throw new AppException(
                        "A specialty you are trying to deactivate is already inactive.",
                        409,
                        "Status Conflict",
                        "The specialty with ID '{$specialtyId}' is already marked as inactive.",
                        null
                    );
                }
                $specialty->status = "inactive";
                $specialty->save();
                $result[] = $specialty;
            }
            DB::commit();
            return $result;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            throw new AppException(
                "One or more specialties you tried to deactivate were not found.",
                404,
                "Specialty Not Found",
                "We could not find a specialty for one of the provided IDs. They may have already been deleted.",
                null
            );
        } catch (AppException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            throw new AppException(
                "An unexpected error occurred during the bulk deactivation of specialties.",
                500,
                "Bulk Deactivation Error",
                "A server-side issue prevented the deactivation process from completing successfully.",
                null
            );
        }
    }

    public function bulkActivateSpecialty(array $specialtyIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($specialtyIds as $specialtyIdData) {
                $specialtyId = $specialtyIdData['specialty_id'] ?? null;
                if (!$specialtyId) {
                    throw new AppException(
                        "An invalid specialty ID was provided in the list.",
                        400,
                        "Invalid Input",
                        "Each entry must contain a valid 'specialty_id' field.",
                        null
                    );
                }
                $specialty = Specialty::findOrFail($specialtyId);
                if ($specialty->status === "active") {
                    throw new AppException(
                        "A specialty you're trying to activate is already active.",
                        409,
                        "Status Conflict",
                        "The specialty with ID '{$specialtyId}' is already marked as active.",
                        null
                    );
                }
                $specialty->status = "active";
                $specialty->save();
                $result[] = $specialty;
            }
            DB::commit();
            return $result;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            throw new AppException(
                "One or more specialties you tried to activate were not found.",
                404,
                "Specialty Not Found",
                "We couldn't find a specialty for one of the provided IDs. They may have already been deleted.",
                null
            );
        } catch (AppException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            throw new AppException(
                "An unexpected error occurred during the bulk activation of specialties.",
                500,
                "Bulk Activation Error",
                "A server-side issue prevented the activation process from completing successfully.",
                null
            );
        }
    }

    public function bulkDeleteSpecialty($specialtyIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($specialtyIds as $specialtyIdData) {
                $specialtyId = $specialtyIdData['specialty_id'] ?? null;
                if (!$specialtyId) {
                    throw new AppException(
                        "An invalid specialty ID was provided in the list.",
                        400,
                        "Invalid Input",
                        "Each entry must contain a valid 'specialty_id' field.",
                        null
                    );
                }
                $specialty = Specialty::findOrFail($specialtyId);
                $specialty->delete();
                $result[] = $specialty;
            }
            DB::commit();
            return $result;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            throw new AppException(
                "One or more specialties you tried to delete were not found.",
                404,
                "Specialty Not Found",
                "We couldn't find a specialty for one of the provided IDs. They may have already been deleted.",
                null
            );
        } catch (AppException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            throw new AppException(
                "An unexpected error occurred during the bulk deletion of specialties.",
                500,
                "Bulk Deletion Error",
                "A server-side issue prevented the deletion process from completing successfully.",
                null
            );
        }
    }
}
