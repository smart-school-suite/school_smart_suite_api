<?php

namespace App\Services\Teacher;

use App\Exceptions\AppException;
use App\Models\Specialty;
use App\Models\TeacherSpecailtyPreference;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TeacherSpecialtyPreferenceService
{
    public function getTeacherPreference($teacherId, $currentSchool)
    {
        $preferences = TeacherSpecailtyPreference::where("school_branch_id", $currentSchool->id)
            ->where("teacher_id", $teacherId)
            ->with([
                'specailty:id,specialty_name,level_id',
                'specailty.level:id,name,level'
            ])
            ->get();

        if ($preferences->isEmpty()) {
            throw new AppException(
                "No specialty preferences found for teacher ID '{$teacherId}' at school branch ID '{$currentSchool->id}'.",
                404,
                "No Preferences Assigned 🧑‍🏫",
                "We couldn't find any defined specialty or subject preferences for this teacher. Please ensure their preferred teaching areas have been properly set up and saved.",
                null
            );
        }

        $formatted = $preferences->map(function ($preference) {
            $specialty = $preference->specailty;
            $level = $specialty->level;

            return [
                'id' => $preference->id,
                'specialty_id' => $specialty->id,
                'specialty_name' => $specialty->specialty_name,
                'level_name' => $level?->name,
                'level' => $level?->level,
            ];
        });

        return $formatted;
    }
    public function getAvailableSpecialtiesForTeacher($teacherId, $currentSchool)
    {
        $assignedSpecialtyIds = TeacherSpecailtyPreference::where("school_branch_id", $currentSchool->id)
            ->where("teacher_id", $teacherId)
            ->pluck("specialty_id");

        $availableSpecialties = Specialty::with('level:id,name,level')
            ->where("school_branch_id", $currentSchool->id)
            ->whereNotIn("id", $assignedSpecialtyIds)
            ->select("id", "specialty_name", "level_id")
            ->get()
            ->map(function ($specialty) {
                return [
                    'id' => $specialty->id,
                    'specialty_name' => $specialty->specialty_name,
                    'level_name' => $specialty->level?->name,
                    'level' => $specialty->level?->level,
                ];
            });

        if ($availableSpecialties->isEmpty()) {
            throw new AppException(
                "No additional specialties are available for teacher ID '{$teacherId}' to choose from at school branch ID '{$currentSchool->id}'.",
                404,
                "All Specialties Assigned ✅",
                "This teacher has already been assigned all available specialties in the system. There are no other specialties remaining to be added as a preference.",
                null
            );
        }

        return $availableSpecialties;
    }
    public function removeTeacherSpecialtyPreference($currentSchool, array $preferences)
    {
        try {
            DB::beginTransaction();

            $preferenceIds = collect($preferences)->pluck('preference_id');

            $validPreferences = TeacherSpecailtyPreference::where("school_branch_id", $currentSchool->id)
                ->whereIn("id", $preferenceIds)
                ->pluck('id');

            if ($validPreferences->isEmpty() && $preferenceIds->isNotEmpty()) {
                DB::rollBack();
                throw new AppException(
                    "None of the provided preference IDs were found or belong to school branch ID '{$currentSchool->id}'. IDs checked: " . $preferenceIds->implode(', '),
                    404,
                    "Preferences Not Found or Invalid 🚫",
                    "We couldn't locate any of the requested teacher specialty preferences to remove. Please ensure the IDs are correct and belong to a teacher at your school.",
                    null
                );
            }

            $deletedCount = TeacherSpecailtyPreference::destroy($validPreferences);

            if ($deletedCount !== $validPreferences->count()) {
                DB::rollBack();
                throw new AppException(
                    "Attempted to delete {$validPreferences->count()} preferences but only {$deletedCount} were affected. Database inconsistency detected.",
                    500,
                    "Incomplete Removal ❌",
                    "There was an issue removing all the requested preferences. Some were successfully removed, but the system reported a partial failure. Please contact support.",
                    null
                );
            }

            DB::commit();
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            throw new AppException(
                "Database transaction failed during preference removal: " . $e->getMessage(),
                500,
                "Removal Failed Due to System Error 🛑",
                "We were unable to complete the removal of the teacher's specialty preferences due to a system error. The operation has been rolled back. Please try again or contact support.",
                null
            );
        }
    }
    public function bulkAddTeacherSpecialtyPreference($currentSchool, array $data)
    {
        $teacherIdsCollection = collect($data['teacherIds'] ?? [])->pluck('teacher_id');
        $specialtyIdsCollection = collect($data['specialtyIds'] ?? [])->pluck('specialty_id');

        if ($teacherIdsCollection->isEmpty() || $specialtyIdsCollection->isEmpty()) {
            throw new AppException(
                "Missing teacher or specialty IDs in the request data.",
                400,
                "Incomplete Request Data 📝",
                "To perform a bulk add, you must provide a list of both Teacher IDs and Specialty IDs. Please check your input and try again.",
                null
            );
        }

        $teacherIds = $teacherIdsCollection->all();
        $specialtyIds = $specialtyIdsCollection->all();

        try {
            $existingPreferences = TeacherSpecailtyPreference::where('school_branch_id', $currentSchool->id)
                ->whereIn('teacher_id', $teacherIds)
                ->whereIn('specialty_id', $specialtyIds)
                ->get(['teacher_id', 'specialty_id'])
                ->map(fn($item) => "{$item->teacher_id}-{$item->specialty_id}")
                ->toArray();

            $insertData = [];
            foreach ($teacherIds as $teacherId) {
                foreach ($specialtyIds as $specialtyId) {
                    $uniqueKey = "{$teacherId}-{$specialtyId}";
                    if (!in_array($uniqueKey, $existingPreferences)) {
                        $insertData[] = [
                            'id' => Str::uuid(),
                            'teacher_id' => $teacherId,
                            'specialty_id' => $specialtyId,
                            'school_branch_id' => $currentSchool->id
                        ];
                    }
                }
            }

            if (!empty($insertData)) {
                TeacherSpecailtyPreference::insert($insertData);
                return true;
            }

            throw new AppException(
                "All requested specialty preferences already exist for these teachers at school branch ID '{$currentSchool->id}'.",
                409,
                "Preferences Already Exist 🔄",
                "The combination of teachers and specialties you attempted to add are already recorded as preferences. No new records were created.",
                null
            );
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "Database insert failed during bulk specialty preference addition: " . $e->getMessage(),
                500,
                "Bulk Add Failed Due to System Error 🛑",
                "We were unable to complete the bulk assignment of specialty preferences due to a system error. Please review the data and contact support if the issue persists.",
                null
            );
        }
    }
    public function bulkRemoveTeacherSpecialtyPreference($currentSchool, array $data)
    {
        $teacherIds = $data['teacherIds'] ?? [];
        $specialtyIds = $data['specialtyIds'] ?? [];

        if (empty($teacherIds) || empty($specialtyIds)) {
            throw new AppException(
                "Missing teacher or specialty IDs in the request data for bulk removal.",
                400,
                "Incomplete Removal Request 🛑",
                "To perform a bulk removal, you must provide a list of both Teacher IDs and Specialty IDs. Please check your input and try again.",
                null
            );
        }

        try {
            $deletedCount = TeacherSpecailtyPreference::where('school_branch_id', $currentSchool->id)
                ->whereIn('teacher_id', $teacherIds)
                ->whereIn('specialty_id', $specialtyIds)
                ->delete();

            if ($deletedCount === 0) {

                throw new AppException(
                    "No matching specialty preferences were found for removal. Teacher IDs: " . implode(',', $teacherIds) . " | Specialty IDs: " . implode(',', $specialtyIds),
                    404,
                    "No Preferences to Remove 🗑️",
                    "The system found no existing specialty preferences matching the combination of teachers and specialties you specified at your school. No records were deleted.",
                    null
                );
            }

            return true;
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "Database deletion failed during bulk specialty preference removal: " . $e->getMessage(),
                500,
                "Bulk Removal Failed Due to System Error ❌",
                "We were unable to complete the bulk removal of specialty preferences due to a system error. Please try again or contact support.",
                null
            );
        }
    }
}
