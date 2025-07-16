<?php

namespace App\Services;

use App\Models\Specialty;
use App\Models\TeacherSpecailtyPreference;
use Exception;
use Illuminate\Support\Facades\DB;

class TeacherSpecailtyPreferenceService
{
    // Implement your logic here

    public function getTeacherPreference($teacherId, $currentSchool)
    {
        $preferences = TeacherSpecailtyPreference::where("school_branch_id", $currentSchool->id)
            ->where("teacher_id", $teacherId)
            ->with([
                'specailty:id,specialty_name,level_id',
                'specailty.level:id,name,level'
            ])
            ->get();

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

        TeacherSpecailtyPreference::destroy($validPreferences);

        DB::commit();
    } catch (Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
}
