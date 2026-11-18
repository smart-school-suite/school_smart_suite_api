<?php

namespace App\Services\Specialty;

use App\Exceptions\AppException;
use App\Models\Educationlevels;
use App\Models\SchoolBranchSetting;
use App\Models\SettingDefination;
use App\Models\Specialty;
use App\Services\Level\LevelFeeSettings;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Events\Actions\AdminActionEvent;

class SpecialtyService
{
    /**
     * Creates a specialty at the chosen level. When "Automatically Create
     * Specialties Across All Levels" (specialties.auto_generate_levels) is
     * on, it is also created at every other level of the school's level
     * system (level.start), so creating Software Engineering at Level 200
     * also creates it at Level 100, 300 and so on. Levels where it already
     * exists are left as they are.
     *
     * Fees: the chosen level takes the fees given, or the level's fees from
     * "Registration/Tuition Fees by Academic Level" when none are given. The
     * other levels always take their own level's fees, since the fees given
     * were for the chosen level.
     *
     * The created specialty carries created_for_levels: the other levels it
     * was created at.
     */
    public function createSpecialty(array $data, object $currentSchool, $authAdmin)
    {
        try {
            $name = trim($data['specialty_name']);
            if ($this->specialtyExists($currentSchool->id, $name, $data['department_id'], $data['level_id'])) {
                throw new AppException(
                    "A specialty with the same name, level, and department already exists.",
                    409,
                    "Duplicate Specialty",
                    "You are trying to create a specialty that already exists. Please check the details and try again.",
                    null
                );
            }

            return DB::transaction(function () use ($data, $currentSchool, $name) {
                $levelFees = app(LevelFeeSettings::class);
                $specialty = $this->newSpecialty($currentSchool->id, $name, $data, $data['level_id'],
                    $this->givenFee($data, "registration_fee")
                        ?? $levelFees->feeFor($currentSchool->id, $data["level_id"], LevelFeeSettings::REGISTRATION),
                    $this->givenFee($data, "school_fee")
                        ?? $levelFees->feeFor($currentSchool->id, $data["level_id"], LevelFeeSettings::TUITION),
                );

                $createdFor = [];
                if ($this->autoGeneratesLevels($currentSchool->id)) {
                    $levels = $this->levelSystem($currentSchool->id);
                    if ($levels->contains('id', $data['level_id'])) {
                        foreach ($levels->where('id', '!=', $data['level_id']) as $level) {
                            if ($this->specialtyExists($currentSchool->id, $name, $data['department_id'], $level->id)) {
                                continue;
                            }
                            $this->newSpecialty($currentSchool->id, $name, $data, $level->id,
                                $levelFees->feeFor($currentSchool->id, $level->id, LevelFeeSettings::REGISTRATION),
                                $levelFees->feeFor($currentSchool->id, $level->id, LevelFeeSettings::TUITION),
                            );
                            $createdFor[] = ['level_id' => $level->id, 'name' => $level->name, 'level' => $level->level];
                        }
                    }
                }

                $specialty->setAttribute('created_for_levels', $createdFor);
                return $specialty;
            });
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

    private function newSpecialty(string $schoolBranchId, string $name, array $data, string $levelId, ?float $registrationFee, ?float $tuitionFee): Specialty
    {
        $specialty = new Specialty();
        $specialty->school_branch_id = $schoolBranchId;
        $specialty->department_id = $data["department_id"];
        $specialty->specialty_name = $name;
        $specialty->registration_fee = $registrationFee ?? 0;
        $specialty->school_fee = $tuitionFee ?? 0;
        $specialty->description = $data["description"] ?? null;
        $specialty->level_id = $levelId;
        $specialty->status = "active";
        $specialty->save();

        return $specialty;
    }

    private function specialtyExists(string $schoolBranchId, string $name, string $departmentId, string $levelId): bool
    {
        return Specialty::where("school_branch_id", $schoolBranchId)
            ->where("specialty_name", $name)
            ->where("level_id", $levelId)
            ->where("department_id", $departmentId)
            ->exists();
    }

    /** specialties.auto_generate_levels for the branch, else the setting's default. */
    private function autoGeneratesLevels(string $schoolBranchId): bool
    {
        $setting = SchoolBranchSetting::where('school_branch_id', $schoolBranchId)
            ->whereHas('settingDefination', fn($query) => $query->where('key', 'specialties.auto_generate_levels'))
            ->first();
        $value = $setting
            ? $setting->value
            : SettingDefination::where('key', 'specialties.auto_generate_levels')->value('default_value');

        return filter_var($value ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    /** The levels of the branch's level system (level.start), lowest first. */
    private function levelSystem(string $schoolBranchId)
    {
        $system = SchoolBranchSetting::where('school_branch_id', $schoolBranchId)
            ->whereHas('settingDefination', fn($query) => $query->where('key', 'level.start'))
            ->first()?->value;

        if (!$system) {
            return collect();
        }

        return Educationlevels::whereHas('levelType', fn($query) => $query->where('program_name', $system))
            ->get()
            ->sortBy(fn($level) => (int) $level->level)
            ->values();
    }
    public function updateSpecialty(array $data, object $currentSchool, string $specialtyId, object $authAdmin)
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
    public function deleteSpecialty(object $currentSchool, string $specialtyId, object $authAdmin)
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
    public function getSpecialties(object $currentSchool)
    {
        $specialtyData = Specialty::where("school_branch_id", $currentSchool->id)
            ->with(['level', 'department'])
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
    public function getSpecailtyDetails(object $currentSchool, string $specialtyId)
    {
        $specialty = Specialty::where("school_branch_id", $currentSchool->id)
            ->with([
                'level',
                'department',
            ])
            ->withCount([
                'courses as courses_count',
                'specialtyHall as halls_count',
                'student as students_count',
                'teachers as teachers_count'
            ])
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

        $specialty->total_halls = $specialty->halls_count ?? 0;
        $specialty->total_students = $specialty->students_count ?? 0;
        $specialty->total_teachers = $specialty->teachers_count ?? 0;
        $specialty->total_courses = $specialty->courses_count ?? 0;

        return $specialty;
    }
    public function deactivateSpecialty(string $specialtyId, object $currentSchool, object $authAdmin)
    {
        try {
            $specialty = Specialty::where("school_branch_id", $currentSchool->id)
                ->findOrFail($specialtyId);
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
    public function activateSpecialty(string $specialtyId, object $currentSchool, object $authAdmin)
    {
        try {
            $specialty = Specialty::where("school_branch_id", $currentSchool->id)
                ->findOrFail($specialtyId);
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
    public function bulkUpdateSpecialty(array $updateDataList, object $currentSchool, object $authAdmin)
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
                $specialty = Specialty::where("school_branch_id", $currentSchool->id)
                    ->findOrFail($specialtyId);
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
    public function bulkDeactivateSpecialty(array $specialtyIds, object $currentSchool, object $authAdmin)
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
                $specialty = Specialty::where("school_branch_id", $currentSchool->id)
                    ->findOrFail($specialtyId);
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
    public function bulkActivateSpecialty(array $specialtyIds, object $currentSchool, object $authAdmin)
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
                $specialty = Specialty::where("school_branch_id", $currentSchool->id)
                    ->findOrFail($specialtyId);
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
    public function bulkDeleteSpecialty(array $specialtyIds, object $currentSchool, object $authAdmin)
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
                $specialty = Specialty::where("school_branch_id", $currentSchool->id)
                    ->findOrFail($specialtyId);
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
    public function getSpecialtyLevel(object $currentSchool)
    {
        $specialties = Specialty::where("school_branch_id", $currentSchool->id)
            ->with(['level'])
            ->get();

        $groupedSpecialties = $specialties->groupBy('level_id')
            ->map(function ($items, $levelId) {
                $firstItem = $items->first();
                return [
                    'level_id' => $levelId,
                    'level_name' => $firstItem->level->name ?? 'N/A',
                    'level' => $firstItem->level->level ?? 'N/A',
                    'program_name' => $firstItem->level->program_name ?? 'N/A',
                    'specialties' => $items->map(function ($specialty) {
                        return [
                            'id' => $specialty->id,
                            'specialty_name' => $specialty->specialty_name,
                        ];
                    })->values()
                ];
            })
            ->values();
        return $groupedSpecialties;
    }
    private function givenFee(array $data, string $key): ?float
    {
        $value = $data[$key] ?? null;

        return $value === null || $value === '' ? null : (float) $value;
    }
}
