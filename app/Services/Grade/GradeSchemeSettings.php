<?php

namespace App\Services\Grade;

use App\Exceptions\AppException;
use App\Models\LetterGrade;
use App\Models\SchoolBranchSetting;
use App\Models\SettingDefination;
use Illuminate\Support\Collection;

/**
 * The grading rules a branch sets in Grade Settings, enforced wherever a
 * grade scale is written (create, update, copy, import) and used to build
 * the generated scale the grade scale form starts from:
 *
 *   "Defined Letter Grade Scheme" (grade.allowed_letter_grades): the letter
 *       grades a grade scale may use.
 *   "Maximum Attainable GPA" (grade.max_gpa): grade points never exceed it.
 */
class GradeSchemeSettings
{
    public const LETTER_GRADES = 'grade.allowed_letter_grades';
    public const MAX_GPA = 'grade.max_gpa';
    public const DEFAULT_MAX_GPA = 4.0;

    /** @var array<string, mixed> */
    private array $cache = [];

    /**
     * Ids of the letter grades the scheme allows. The saved value is a list
     * of ids; the seeded default is a list of letter grade rows. An empty or
     * unreadable scheme allows every letter grade.
     *
     * @return array<int, string>
     */
    public function allowedLetterGradeIds(string $schoolBranchId): array
    {
        return $this->cache["letters:{$schoolBranchId}"] ??= (function () use ($schoolBranchId) {
            $all = LetterGrade::pluck('id')->all();
            $value = $this->value($schoolBranchId, self::LETTER_GRADES);
            if (is_string($value)) {
                $value = json_decode($value, true);
            }

            $ids = collect(is_array($value) ? $value : [])
                ->map(fn($item) => is_array($item) ? ($item['id'] ?? null) : $item)
                ->filter(fn($id) => is_string($id) && $id !== '')
                ->intersect($all)
                ->unique()
                ->values()
                ->all();

            return $ids === [] ? $all : $ids;
        })();
    }

    public function isLetterGradeAllowed(string $schoolBranchId, ?string $letterGradeId): bool
    {
        return $letterGradeId !== null && in_array($letterGradeId, $this->allowedLetterGradeIds($schoolBranchId), true);
    }

    /** The branch's Maximum Attainable GPA (the setting's default when unset). */
    public function maxGpa(string $schoolBranchId): float
    {
        return $this->cache["gpa:{$schoolBranchId}"] ??= (function () use ($schoolBranchId) {
            $value = $this->value($schoolBranchId, self::MAX_GPA)
                ?? SettingDefination::where('key', self::MAX_GPA)->value('default_value');

            return is_numeric($value) && (float) $value > 0 ? (float) $value : self::DEFAULT_MAX_GPA;
        })();
    }

    /**
     * Refuse grades that break the scheme.
     *
     * @param iterable<array{letter_grade_id?: ?string, grade_points?: mixed}> $grades
     * @throws AppException naming the letters and the limit.
     */
    public function assertGradesAllowed(string $schoolBranchId, iterable $grades): void
    {
        $grades = collect($grades);
        $letters = LetterGrade::whereIn('id', $grades->pluck('letter_grade_id')->filter()->unique())->pluck('letter_grade', 'id');

        $notInScheme = $grades
            ->pluck('letter_grade_id')
            ->filter()
            ->unique()
            ->reject(fn($id) => $this->isLetterGradeAllowed($schoolBranchId, $id))
            ->map(fn($id) => $letters[$id] ?? $id)
            ->values();

        if ($notInScheme->isNotEmpty()) {
            throw new AppException(
                "Letter grades not in the school's scheme: {$notInScheme->implode(', ')}.",
                422,
                "Letter Grade Not Allowed",
                "{$notInScheme->implode(', ')} " . ($notInScheme->count() === 1 ? 'is' : 'are') . " not part of your Defined Letter Grade Scheme. Remove " . ($notInScheme->count() === 1 ? 'it' : 'them') . " from the grade scale, or add " . ($notInScheme->count() === 1 ? 'it' : 'them') . " under Settings › Grade Settings › Defined Letter Grade Scheme.",
                null
            );
        }

        $maxGpa = $this->maxGpa($schoolBranchId);
        $tooHigh = $grades
            ->filter(fn($grade) => isset($grade['grade_points']) && is_numeric($grade['grade_points']) && (float) $grade['grade_points'] > $maxGpa + 0.00001)
            ->map(fn($grade) => ($letters[$grade['letter_grade_id'] ?? ''] ?? 'A grade') . ' (' . $this->format((float) $grade['grade_points']) . ')')
            ->values();

        if ($tooHigh->isNotEmpty()) {
            throw new AppException(
                "Grade points above the maximum attainable GPA of {$this->format($maxGpa)}: {$tooHigh->implode(', ')}.",
                422,
                "Grade Points Too High",
                "Grade points cannot exceed your Maximum Attainable GPA of {$this->format($maxGpa)}. Lower the points of {$tooHigh->implode(', ')}.",
                null
            );
        }
    }

    public function format(float $value): string
    {
        return number_format($value, 2, '.', '');
    }

    private function value(string $schoolBranchId, string $key): mixed
    {
        return SchoolBranchSetting::where('school_branch_id', $schoolBranchId)
            ->whereHas('settingDefination', fn($query) => $query->where('key', $key))
            ->first()?->value;
    }
}
