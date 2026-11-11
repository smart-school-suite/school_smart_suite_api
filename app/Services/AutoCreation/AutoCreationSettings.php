<?php

namespace App\Services\AutoCreation;

use App\Models\SchoolBranchSetting;

/**
 * A school branch's automatic creation settings for one kind of record.
 * The same five settings exist for each kind, under its key prefix:
 *
 *  - semester.auto_create_*       (Semester Settings)
 *  - academic_year.auto_create_*  (Academic Year Settings)
 *
 * Settings (key = prefix + name):
 *  - mode: disabled | same_specialty | across_specialties.
 *  - levels: the level ids that may receive automatically created records.
 *  - across_specialties: other specialties of the department receive them,
 *    at the levels the original specialty also has.
 *  - across_all_levels: other specialties of the department receive them,
 *    at every applicable level.
 *  - require_students: only specialty levels with enrolled students do.
 *
 * The two "across" switches are independent and are kept in step with the
 * mode by the setting handlers: either switch on means across_specialties.
 */
class AutoCreationSettings
{
    public const SEMESTER = 'semester.auto_create_';
    public const ACADEMIC_YEAR = 'academic_year.auto_create_';
    public const PREFIXES = [self::SEMESTER, self::ACADEMIC_YEAR];

    public const MODE = 'mode';
    public const LEVELS = 'levels';
    public const ACROSS_SPECIALTIES = 'across_specialties';
    public const ACROSS_ALL_LEVELS = 'across_all_levels';
    public const REQUIRE_STUDENTS = 'require_students';

    public const NAMES = [self::MODE, self::LEVELS, self::ACROSS_SPECIALTIES, self::ACROSS_ALL_LEVELS, self::REQUIRE_STUDENTS];
    public const SWITCHES = [self::ACROSS_SPECIALTIES, self::ACROSS_ALL_LEVELS];

    public const MODE_DISABLED = 'disabled';
    public const MODE_SAME_SPECIALTY = 'same_specialty';
    public const MODE_ACROSS_SPECIALTIES = 'across_specialties';
    public const MODES = [self::MODE_DISABLED, self::MODE_SAME_SPECIALTY, self::MODE_ACROSS_SPECIALTIES];

    /** The name of the setting group, as shown to admins. */
    public const LABELS = [
        self::SEMESTER => 'Automatic Semester Creation',
        self::ACADEMIC_YEAR => 'Automatic Academic Year Creation',
    ];

    public function __construct(
        public readonly string $prefix,
        public readonly string $mode = self::MODE_DISABLED,
        public readonly array $levelIds = [],
        public readonly bool $acrossSpecialties = false,
        public readonly bool $acrossAllLevels = false,
        public readonly bool $requireStudents = false,
    ) {}

    public static function forBranch(string $schoolBranchId, string $prefix): self
    {
        $values = self::settings($schoolBranchId, $prefix)->map(fn($setting) => $setting->value);
        $mode = $values[self::MODE] ?? null;

        return new self(
            prefix: $prefix,
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

    public static function key(string $prefix, string $name): string
    {
        return $prefix . $name;
    }

    /** The prefix a setting key belongs to, or null for any other key. */
    public static function prefixOf(?string $key): ?string
    {
        foreach (self::PREFIXES as $prefix) {
            if ($key !== null && str_starts_with($key, $prefix)) {
                return $prefix;
            }
        }

        return null;
    }

    public static function label(string $prefix): string
    {
        return self::LABELS[$prefix] ?? 'Automatic Creation';
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

    /** The branch's setting rows of one group, keyed by setting name (without the prefix). */
    public static function settings(string $schoolBranchId, string $prefix, array $names = self::NAMES)
    {
        $keys = array_map(fn($name) => self::key($prefix, $name), $names);

        return SchoolBranchSetting::where('school_branch_id', $schoolBranchId)
            ->with('settingDefination:id,key')
            ->whereHas('settingDefination', fn($query) => $query->whereIn('key', $keys))
            ->get()
            ->keyBy(fn($setting) => substr($setting->settingDefination->key, strlen($prefix)));
    }
}
