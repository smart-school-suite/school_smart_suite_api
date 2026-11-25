<?php

namespace App\Services\AutoCreation;

use App\Models\SchoolBranchSetting;

/**
 * A school branch's automatic creation settings for one kind of record.
 * The same settings exist for each kind, under its key prefix:
 *
 *  - semester.auto_create_*       (Semester Settings)
 *  - academic_year.auto_create_*  (Academic Year Settings)
 *
 * Settings (key = prefix + name):
 *  - enabled: the main switch.
 *  - levels: the level ids that may receive automatically created records.
 *  - one of four scopes, at most one on at a time:
 *      across_levels                every specialty of the branch;
 *      across_specialty             the same specialty, in any department;
 *      across_department_levels     every specialty of the same department;
 *      across_department_specialty  the same specialty, same department.
 *  - require_students: only specialty levels with enrolled students.
 */
class AutoCreationSettings
{
    public const SEMESTER = 'semester.auto_create_';
    public const ACADEMIC_YEAR = 'academic_year.auto_create_';
    public const PREFIXES = [self::SEMESTER, self::ACADEMIC_YEAR];

    public const ENABLED = 'enabled';
    public const LEVELS = 'levels';
    public const REQUIRE_STUDENTS = 'require_students';

    public const ACROSS_LEVELS = 'across_levels';
    public const ACROSS_SPECIALTY = 'across_specialty';
    public const ACROSS_DEPARTMENT_LEVELS = 'across_department_levels';
    public const ACROSS_DEPARTMENT_SPECIALTY = 'across_department_specialty';

    public const SCOPES = [
        self::ACROSS_LEVELS,
        self::ACROSS_SPECIALTY,
        self::ACROSS_DEPARTMENT_LEVELS,
        self::ACROSS_DEPARTMENT_SPECIALTY,
    ];

    public const DEFAULT_SCOPE = self::ACROSS_DEPARTMENT_SPECIALTY;

    public const NAMES = [self::ENABLED, self::LEVELS, ...self::SCOPES, self::REQUIRE_STUDENTS];

    public const LABELS = [
        self::SEMESTER => 'Automatic Semester Creation',
        self::ACADEMIC_YEAR => 'Automatic Academic Year Creation',
    ];

    public function __construct(
        public readonly string $prefix,
        public readonly bool $active = false,
        public readonly ?string $scope = null,
        public readonly array $levelIds = [],
        public readonly bool $requireStudents = false,
    ) {}

    public static function forBranch(string $schoolBranchId, string $prefix): self
    {
        $values = self::settings($schoolBranchId, $prefix)->map(fn($setting) => $setting->value);
        $isOn = fn(string $name) => filter_var($values[$name] ?? false, FILTER_VALIDATE_BOOLEAN);

        return new self(
            prefix: $prefix,
            active: $isOn(self::ENABLED),
            scope: collect(self::SCOPES)->first($isOn),
            levelIds: self::levelIds($values[self::LEVELS] ?? []),
            requireStudents: $isOn(self::REQUIRE_STUDENTS),
        );
    }

    public function enabled(): bool
    {
        return $this->active && $this->scope !== null;
    }

    public function sameDepartment(): bool
    {
        return in_array($this->scope, [self::ACROSS_DEPARTMENT_LEVELS, self::ACROSS_DEPARTMENT_SPECIALTY], true);
    }

    public function sameSpecialty(): bool
    {
        return in_array($this->scope, [self::ACROSS_SPECIALTY, self::ACROSS_DEPARTMENT_SPECIALTY], true);
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
