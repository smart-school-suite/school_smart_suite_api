<?php

namespace App\Services\Semester;

use App\Models\SchoolBranchSetting;

/**
 * Reads which automatic semester creation mode a school branch uses when a
 * semester is created for one level of a specialty.
 *
 *  - ALL_LEVELS:           create the same semester for every other level of the specialty.
 *  - LEVELS_WITH_STUDENTS: create it only for the other levels that have active students.
 *
 * When neither is enabled no semester is created automatically.
 */
class SemesterAutoCreateSetting
{
    public const ALL_LEVELS = 'semester.auto_create_all_levels';
    public const LEVELS_WITH_STUDENTS = 'semester.auto_create_levels_with_students';

    public const KEYS = [self::ALL_LEVELS, self::LEVELS_WITH_STUDENTS];

    /**
     * The enabled mode's key, or null when automatic creation is off.
     * If both were somehow left on, the stricter "levels with students" wins.
     */
    public function modeFor(string $schoolBranchId): ?string
    {
        $values = SchoolBranchSetting::where('school_branch_id', $schoolBranchId)
            ->with('settingDefination:id,key')
            ->whereHas('settingDefination', fn($query) => $query->whereIn('key', self::KEYS))
            ->get()
            ->mapWithKeys(fn($setting) => [
                $setting->settingDefination->key => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            ]);

        if ($values[self::LEVELS_WITH_STUDENTS] ?? false) {
            return self::LEVELS_WITH_STUDENTS;
        }

        if ($values[self::ALL_LEVELS] ?? false) {
            return self::ALL_LEVELS;
        }

        return null;
    }
}
