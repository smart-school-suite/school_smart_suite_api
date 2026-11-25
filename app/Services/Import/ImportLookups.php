<?php

namespace App\Services\Import;

use App\Models\Courses;
use App\Models\Department;
use App\Models\Educationlevels;
use App\Models\Hall;
use App\Models\HallType;
use App\Models\Gender;
use App\Models\Qualification;
use App\Models\SchoolBranchSetting;
use App\Models\Specialty;
use App\Models\Teacher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Resolves what people type in a spreadsheet (a gender, a level, a teacher's
 * name) to the records it means, the same way for every import. Matching
 * ignores case and extra spaces. Each list is loaded once per import.
 *
 * Methods return null when nothing matches and throw ImportRowException when
 * the value is ambiguous, so the row is reported with the reason.
 */
class ImportLookups
{
    private array $cache = [];

    public function __construct(private readonly string $schoolBranchId)
    {
    }

    public static function key(mixed $value): string
    {
        return Str::lower(preg_replace('/\s+/', ' ', trim((string) $value)));
    }

    /** "Male", "male", "M" -> the gender's id. */
    public function genderId(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $genders = $this->remember('genders', fn() => Gender::all(['id', 'name']));
        $key = self::key($value);

        $match = $genders->first(fn($gender) => self::key($gender->name) === $key)
            ?? (strlen($key) === 1
                ? $genders->first(fn($gender) => str_starts_with(self::key($gender->name), $key))
                : null);

        return $match?->id;
    }

    /** Names of the genders, for messages: "Male or Female". */
    public function genderNames(): string
    {
        return $this->remember('genders', fn() => Gender::all(['id', 'name']))
            ->pluck('name')
            ->join(', ', ' or ');
    }

    /**
     * The levels of the branch's level system (the level.start setting),
     * or every level when the branch has not chosen one.
     */
    public function levels(): Collection
    {
        return $this->remember('levels', function () {
            $system = SchoolBranchSetting::where('school_branch_id', $this->schoolBranchId)
                ->whereHas('settingDefination', fn($query) => $query->where('key', 'level.start'))
                ->first()?->value;

            return Educationlevels::query()
                ->when($system, fn($query) => $query->whereHas('levelType', fn($q) => $q->where('program_name', $system)))
                ->get(['id', 'name', 'level', 'program_name']);
        });
    }

    /** "Level One", "level_one" or "200" -> the level's id in the branch's level system. */
    public function levelId(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $key = self::key($value);
        $levels = $this->levels();

        $match = $levels->first(fn($level) => self::key($level->name) === $key)
            ?? $levels->first(fn($level) => self::key($level->program_name) === $key)
            ?? $levels->first(fn($level) => (string) $level->level === $key);

        return $match?->id;
    }

    /** "MSc" or "Master of Science" -> the qualification's id. */
    public function qualificationId(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $qualifications = $this->remember('qualifications', fn() => Qualification::all(['id', 'name', 'abbreviation']));
        $key = self::key($value);

        return ($qualifications->first(fn($q) => self::key($q->abbreviation) === $key)
            ?? $qualifications->first(fn($q) => self::key($q->name) === $key))?->id;
    }

    /** A teacher of the branch by full name or email. */
    public function teacher(?string $value): ?Teacher
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $teachers = $this->remember('teachers', fn() => Teacher::where('school_branch_id', $this->schoolBranchId)
            ->get(['id', 'name', 'email', 'school_branch_id']));
        $key = self::key($value);
        $field = str_contains($key, '@') ? 'email' : 'name';

        return $teachers->first(fn($teacher) => self::key($teacher->{$field}) === $key);
    }

    /**
     * A specialty of the branch by name, narrowed by level when given.
     *
     * @throws ImportRowException when the name matches specialties of several levels and no level is given.
     */
    public function specialty(?string $name, ?string $level, string $field = 'specialty'): ?Specialty
    {
        if ($name === null || trim($name) === '') {
            return null;
        }

        $specialties = $this->remember('specialties', fn() => Specialty::where('school_branch_id', $this->schoolBranchId)
            ->get(['id', 'specialty_name', 'level_id', 'school_branch_id']));
        $matches = $specialties->filter(fn($specialty) => self::key($specialty->specialty_name) === self::key($name));

        if ($level !== null && trim($level) !== '') {
            $levelId = $this->levelId($level);
            if (!$levelId) {
                throw ImportRowException::notFound(
                    'Level not found in your school\'s level system. Use a level name such as "' . ($this->levels()->first()?->name ?? 'Level One') . '".',
                    'level',
                    $level
                );
            }
            $matches = $matches->where('level_id', $levelId);
        }

        if ($matches->count() > 1) {
            throw ImportRowException::invalid(
                'Several specialties have this name at different levels. Add the level column to say which one.',
                $field,
                $name
            );
        }

        return $matches->first();
    }

    /**
     * A course of the branch by code or title. Titles shared by several
     * courses are narrowed to the specialty when one is given.
     *
     * @throws ImportRowException when the title is shared and cannot be narrowed.
     */
    public function course(?string $value, ?Specialty $specialty = null): ?Courses
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $courses = $this->remember('courses', fn() => Courses::where('school_branch_id', $this->schoolBranchId)
            ->with('courseSpecialties:id,course_id,specialty_id')
            ->get(['id', 'course_code', 'course_title', 'school_branch_id']));
        $key = self::key($value);

        $byCode = $courses->filter(fn($course) => self::key($course->course_code) === $key);
        $matches = $byCode->isNotEmpty() ? $byCode : $courses->filter(fn($course) => self::key($course->course_title) === $key);

        if ($matches->count() > 1 && $specialty) {
            $matches = $matches->filter(fn($course) => $course->courseSpecialties->contains('specialty_id', $specialty->id));
        }

        if ($matches->count() > 1) {
            throw ImportRowException::invalid(
                'Several courses have this title. Use the course code, or add the specialty and level columns to say which one.',
                'course',
                $value
            );
        }

        return $matches->first();
    }

    /** A department of the branch by name. */
    public function department(?string $name): ?Department
    {
        if ($name === null || trim($name) === '') {
            return null;
        }

        return $this->remember('departments', fn() => Department::where('school_branch_id', $this->schoolBranchId)
            ->get(['id', 'department_name', 'school_branch_id']))
            ->first(fn($department) => self::key($department->department_name) === self::key($name));
    }

    /** A hall of the branch by name. */
    public function hall(?string $name): ?Hall
    {
        if ($name === null || trim($name) === '') {
            return null;
        }

        return $this->remember('halls', fn() => Hall::where('school_branch_id', $this->schoolBranchId)
            ->get(['id', 'name', 'school_branch_id']))
            ->first(fn($hall) => self::key($hall->name) === self::key($name));
    }

    /** "Lecture Hall", "lecture" or "lecture.hall" -> the hall type's id. */
    public function hallTypeId(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $types = $this->remember('hallTypes', fn() => HallType::all(['id', 'name', 'key']));
        $key = self::key($value);

        return ($types->first(fn($type) => self::key($type->name) === $key)
            ?? $types->first(fn($type) => self::key($type->key) === $key)
            ?? $types->first(fn($type) => self::key(str_replace(' hall', '', self::key($type->name))) === $key))?->id;
    }

    /** Names of the hall types, for messages. */
    public function hallTypeNames(): string
    {
        return $this->remember('hallTypes', fn() => HallType::all(['id', 'name', 'key']))
            ->pluck('name')
            ->join(', ', ' or ');
    }

    /**
     * A row of a reference table matched on any of the given columns,
     * ignoring case: semesters, course types, relationships, sources...
     *
     * @param array<int, string> $columns
     */
    public function reference(string $table, array $columns, ?string $value, bool $branchScoped = false): ?object
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $rows = $this->rows($table, $branchScoped);
        $key = self::key($value);

        foreach ($columns as $column) {
            $match = $rows->first(fn($row) => isset($row->{$column}) && self::key($row->{$column}) === $key);
            if ($match) {
                return $match;
            }
        }

        return null;
    }

    /** Values of a reference table's column, for messages: "Father, Mother or Guardian". */
    public function referenceNames(string $table, string $column, bool $branchScoped = false, int $limit = 6): string
    {
        $names = $this->rows($table, $branchScoped)->pluck($column)->filter()->values();
        $shown = $names->take($limit);

        return $shown->join(', ', ' or ') . ($names->count() > $limit ? ', ...' : '');
    }

    /** "First Semester", "first semester", "1" or "Semester 1" -> the semester's id. */
    public function semesterId(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $match = $this->reference('semesters', ['name', 'program_name'], $value);
        if (!$match && preg_match('/^(?:semester\s*)?(\d+)$/i', trim($value), $number)) {
            $match = $this->rows('semesters')->first(fn($row) => (int) $row->count === (int) $number[1]);
        }

        return $match?->id;
    }

    /** "Practical", "practical.course" or "practical" (small typos allowed) -> the course type's id. */
    public function courseTypeId(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $match = $this->reference('course_types', ['name', 'key'], $value);
        if (!$match) {
            $key = self::key($value);
            $match = $this->rows('course_types')->first(function ($row) use ($key) {
                $base = self::key(explode('.', (string) $row->key)[0]);
                return levenshtein($base, $key) <= 2 || levenshtein(self::key($row->name), $key) <= 2;
            });
        }

        return $match?->id;
    }

    /** A guardian of the branch by phone number or, when only one has it, by name. */
    public function guardian(?string $value): ?object
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $rows = $this->rows('parents', true);
        $digits = preg_replace('/\D/', '', $value);

        if (strlen($digits) >= 6) {
            $byPhone = $rows->first(fn($row) => preg_replace('/\D/', '', (string) $row->phone) === $digits);
            if ($byPhone) {
                return $byPhone;
            }
        }

        $byName = $rows->filter(fn($row) => self::key($row->name) === self::key($value));
        if ($byName->count() > 1) {
            throw ImportRowException::invalid(
                'Several guardians have this name. Use the guardian\'s phone number instead.',
                'guardian',
                $value
            );
        }

        return $byName->first();
    }

    /** Forget a cached list after rows changed it (e.g. a teacher was created). */
    public function forget(string $list): void
    {
        unset($this->cache[$list]);
    }

    private function rows(string $table, bool $branchScoped = false): Collection
    {
        return $this->remember("table:{$table}", fn() => DB::table($table)
            ->when($branchScoped, fn($query) => $query->where('school_branch_id', $this->schoolBranchId))
            ->get());
    }

    private function remember(string $key, callable $load): Collection
    {
        return $this->cache[$key] ??= $load();
    }
}
