<?php

namespace App\Services\Semester;

use App\Models\SchoolBranchSetting;

/**
 * A school branch's automatic semester creation settings.
 *
 * Mode (semester.auto_create_mode)
 *  - disabled:           only the selected level gets the semester.
 *  - same_specialty:     the other applicable levels of the same specialty.
 *  - across_specialties: other specialties of the same department as well,
 *                        as chosen by the two switches below.
 *
 * Applicable levels (semester.auto_create_levels): only these level ids
 * receive automatically created semesters.
 *
 * Across specialties (semester.auto_create_across_specialties): other
 * specialties receive the semester at the levels the original specialty
 * also has (matching academic structure).
 *
 * Across all specialties' levels (semester.auto_create_across_all_levels):
 * other specialties receive the semester at every applicable level, whether
 * or not the original specialty has that level.
 *
 * Only include specialties with enrolled students
 * (semester.auto_create_require_students): a target only receives the
 * semester when it has at least one enrolled (non dropped out) student.
 *
 * The two switches are independent, and are kept in step with the mode by
 * the setting handlers: either switch on means mode across_specialties.
 */
class SemesterAutoCreationSettings
{
    public const MODE = 'semester.auto_create_mode';
    public const LEVELS = 'semester.auto_create_levels';
    public const ACROSS_SPECIALTIES = 'semester.auto_create_across_specialties';
    public const ACROSS_ALL_LEVELS = 'semester.auto_create_across_all_levels';
    public const REQUIRE_STUDENTS = 'semester.auto_create_require_students';

    public const KEYS = [self::MODE, self::LEVELS, self::ACROSS_SPECIALTIES, self::ACROSS_ALL_LEVELS, self::REQUIRE_STUDENTS];
    public const SWITCHES = [self::ACROSS_SPECIALTIES, self::ACROSS_ALL_LEVELS];

    public const MODE_DISABLED = 'disabled';
    public const MODE_SAME_SPECIALTY = 'same_specialty';
    public const MODE_ACROSS_SPECIALTIES = 'across_specialties';
    public const MODES = [self::MODE_DISABLED, self::MODE_SAME_SPECIALTY, self::MODE_ACROSS_SPECIALTIES];

    public function __construct(
        public readonly string $mode = self::MODE_DISABLED,
        public readonly array $levelIds = [],
        public readonly bool $acrossSpecialties = false,
        public readonly bool $acrossAllLevels = false,
        public readonly bool $requireStudents = false,
    ) {}

    public static function forBranch(string $schoolBranchId): self
    {
        $values = self::values($schoolBranchId);
        $mode = $values[self::MODE] ?? null;

        return new self(
            mode: in_array($mode, self::MODES, true) ? $mode : self::MODE_DISABLED,
            levelIds: self::levelIds($values[self::LEVELS] ?? []),
            acrossSpecialties: filter_var($values[self::ACROSS_SPECIALTIES] ?? false, FILTER_VALIDATE_BOOLEAN),
            acrossAllLevels: filter_var($values[self::ACROSS_ALL_LEVELS] ?? false, FILTER_VALIDATE_BOOLEAN),
            requireStudents: filter_var($values[self::REQUIRE_STUDENTS] ?? false, FILTER_VALIDATE_BOOLEAN),
        );
    }

    public function enabled(): bool
    {
        return $this->mode !== self::MODE_DISABLED;
    }

    public function includesOtherSpecialties(): bool
    {
        return $this->mode === self::MODE_ACROSS_SPECIALTIES && ($this->acrossSpecialties || $this->acrossAllLevels);
    }

    /** Accepts a list of ids, or a JSON string of one. */
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

    /** The branch's setting rows for these keys, keyed by setting key. */
    public static function settings(string $schoolBranchId, array $keys = self::KEYS)
    {
        return SchoolBranchSetting::where('school_branch_id', $schoolBranchId)
            ->with('settingDefination:id,key')
            ->whereHas('settingDefination', fn($query) => $query->whereIn('key', $keys))
            ->get()
            ->keyBy(fn($setting) => $setting->settingDefination->key);
    }

    private static function values(string $schoolBranchId): array
    {
        return self::settings($schoolBranchId)->map(fn($setting) => $setting->value)->all();
    }
}
