<?php

namespace App\Services\Exam;

use App\Models\Exam\Exam;
use App\Models\GradeScale\SchoolGradeScaleCategory;
use App\Models\SchoolBranchSetting;

/**
 * "Attach Matching Grade Scale" (exam.auto_attach_grade_scale).
 *
 * When on, a newly created exam gets the school's grade scale that fits it:
 * a complete grade scale (it has grades) of the exam's type (CA or Exam)
 * with the exam's max score. It is attached only when exactly one fits;
 * when none or several do, the exam is left for a grade scale to be
 * attached by hand, as before.
 */
class ExamGradeScaleMatcher
{
    public const ENABLED = 'exam.auto_attach_grade_scale';

    /** @var array<string, bool> */
    private array $enabled = [];

    /**
     * Attaches the matching grade scale to an exam that has none.
     *
     * @return array{status: string, grade_scale: ?string} status is attached, none, several or off.
     */
    public function attach(Exam $exam, string $schoolBranchId): array
    {
        if (!$this->enabled($schoolBranchId)) {
            return ['status' => 'off', 'grade_scale' => null];
        }

        if ($exam->grades_category_id) {
            return ['status' => 'attached', 'grade_scale' => null];
        }

        $type = strtolower((string) $exam->examType()->value('type'));

        $matches = SchoolGradeScaleCategory::where('school_branch_id', $schoolBranchId)
            ->whereHas('schoolGradeScale')
            ->whereHas('systemGradeCategory', fn($query) => $query->whereRaw('LOWER(exam_type) = ?', [$type]))
            ->with('systemGradeCategory:id,title')
            ->get()
            ->filter(fn($category) => $category->max_score !== null && abs((float) $category->max_score - (float) $exam->max_score) < 0.0001)
            ->values();

        if ($matches->count() !== 1) {
            return ['status' => $matches->isEmpty() ? 'none' : 'several', 'grade_scale' => null];
        }

        $exam->grades_category_id = $matches[0]->id;
        $exam->save();

        return ['status' => 'attached', 'grade_scale' => $matches[0]->systemGradeCategory?->title];
    }

    private function enabled(string $schoolBranchId): bool
    {
        return $this->enabled[$schoolBranchId] ??= (function () use ($schoolBranchId) {
            $value = SchoolBranchSetting::where('school_branch_id', $schoolBranchId)
                ->whereHas('settingDefination', fn($query) => $query->where('key', self::ENABLED))
                ->first()?->value;

            return $value === null || filter_var($value, FILTER_VALIDATE_BOOLEAN);
        })();
    }
}
