<?php

namespace App\Services\Semester;

use App\Models\SchoolBranchSetting;

/**
 * A school branch's semester synchronization settings: how a newly created
 * semester is copied to other levels and specialties.
 *
 * Mode
 *  - disabled:          only the selected level gets the semester.
 *  - same_programme:    the other levels of the same specialty (same name and department).
 *  - across_programmes: other specialties too, bounded by the scope below.
 *
 * Scope (across_programmes only)
 *  - same_specialty:     specialties with the same name, in any department.
 *  - across_specialties: every specialty in the same department.
 *  - institution:        every specialty in the school branch.
 *
 * Allowed levels: only these level ids receive synchronized semesters.
 * Require students: when on, a target must have active students.
 */
class SemesterSyncSettings
{
    public const MODE = 'semester.sync_mode';
    public const ALLOWED_LEVELS = 'semester.sync_allowed_levels';
    public const SCOPE = 'semester.sync_scope';
    public const REQUIRE_STUDENTS = 'semester.sync_require_students';

    public const KEYS = [self::MODE, self::ALLOWED_LEVELS, self::SCOPE, self::REQUIRE_STUDENTS];

    public const MODE_DISABLED = 'disabled';
    public const MODE_SAME_PROGRAMME = 'same_programme';
    public const MODE_ACROSS_PROGRAMMES = 'across_programmes';
    public const MODES = [self::MODE_DISABLED, self::MODE_SAME_PROGRAMME, self::MODE_ACROSS_PROGRAMMES];

    public const SCOPE_SAME_SPECIALTY = 'same_specialty';
    public const SCOPE_ACROSS_SPECIALTIES = 'across_specialties';
    public const SCOPE_INSTITUTION = 'institution';
    public const SCOPES = [self::SCOPE_SAME_SPECIALTY, self::SCOPE_ACROSS_SPECIALTIES, self::SCOPE_INSTITUTION];

    public function __construct(
        public readonly string $mode = self::MODE_DISABLED,
        public readonly array $allowedLevelIds = [],
        public readonly string $scope = self::SCOPE_SAME_SPECIALTY,
        public readonly bool $requireStudents = false,
    ) {}

    public static function forBranch(string $schoolBranchId): self
    {
        $values = SchoolBranchSetting::where('school_branch_id', $schoolBranchId)
            ->with('settingDefination:id,key')
            ->whereHas('settingDefination', fn($query) => $query->whereIn('key', self::KEYS))
            ->get()
            ->mapWithKeys(fn($setting) => [$setting->settingDefination->key => $setting->value]);

        $mode = $values[self::MODE] ?? null;
        $scope = $values[self::SCOPE] ?? null;

        return new self(
            mode: in_array($mode, self::MODES, true) ? $mode : self::MODE_DISABLED,
            allowedLevelIds: self::levelIds($values[self::ALLOWED_LEVELS] ?? []),
            scope: in_array($scope, self::SCOPES, true) ? $scope : self::SCOPE_SAME_SPECIALTY,
            requireStudents: filter_var($values[self::REQUIRE_STUDENTS] ?? false, FILTER_VALIDATE_BOOLEAN),
        );
    }

    public function enabled(): bool
    {
        return $this->mode !== self::MODE_DISABLED;
    }

    /** Accepts a list of ids, or a JSON string of one (seeded defaults are stored that way). */
    public static function levelIds(mixed $value): array
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        if (!is_array($value)) {
            return [];
        }

        return array_values(array_unique(array_filter($value, fn($id) => is_string($id) && $id !== '')));
    }
}
