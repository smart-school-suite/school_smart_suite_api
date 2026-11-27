<?php

namespace App\Services\Exam;

use App\Exceptions\AppException;
use App\Models\Exam\Exam;
use App\Models\SchoolBranchSetting;
use App\Models\SchoolSemester;
use App\Services\Level\SchoolLevels;
use Illuminate\Support\Collection;

/**
 * "Automatically Create Exams for Matching Semesters" (exam.auto_create).
 *
 * When an exam is created, it is also created for every other specialty
 * whose semester is the same semester (e.g. First Semester) with the same
 * start and end dates, at the levels of "Levels for Automatic Exam
 * Creation" (exam.auto_create_levels). Each copy has the original's dates
 * and exam part of the max score; an Exam adds its own specialty's CA.
 *
 * Each copy passes the same checks as an exam created by hand
 * (ExamCreationPolicy): a specialty without students, without its CA yet,
 * or that already has this exam is skipped, with the reason.
 *
 * Must be called inside the transaction that created the original, so the
 * original and its copies are committed or rolled back together.
 */
class ExamAutoCreationService
{
    public const ENABLED = 'exam.auto_create';

    public function __construct(
        private ExamCreationPolicy $policy,
        private SchoolLevels $levels,
    ) {}

    /**
     * @param float $examPart the max score the original was created with, without the CA's.
     * @return array{created: Collection<int, Exam>, skipped: array<int, array>}
     */
    public function createFor(Exam $original, SchoolSemester $semester, float $examPart, object $currentSchool): array
    {
        $result = ['created' => collect(), 'skipped' => []];
        $branchId = $currentSchool->id;

        if (!$this->enabled($branchId)) {
            return $result;
        }

        $levelIds = $this->levels->autoExamLevels($branchId)->pluck('id')->all();

        $targets = SchoolSemester::where('school_branch_id', $branchId)
            ->where('id', '!=', $semester->id)
            ->where('semester_id', $semester->semester_id)
            ->whereDate('start_date', $this->date($semester->start_date))
            ->whereDate('end_date', $this->date($semester->end_date))
            ->with(['schoolYear.specialty.level'])
            ->get()
            ->filter(fn(SchoolSemester $target) => in_array($target->schoolYear?->specialty?->level_id, $levelIds, true));

        foreach ($targets as $target) {
            try {
                $allowed = $this->policy->validated($currentSchool, [
                    'school_semester_id' => $target->id,
                    'exam_type_id' => $original->exam_type_id,
                    'start_date' => $this->date($original->start_date),
                    'end_date' => $this->date($original->end_date),
                ]);
            } catch (AppException $e) {
                $result['skipped'][] = $this->describe($target) + [
                    'reason' => $e->getTitle(),
                    'detail' => $e->getDescription(),
                ];
                continue;
            }

            $exam = new Exam();
            $exam->school_branch_id = $branchId;
            $exam->start_date = $original->start_date;
            $exam->end_date = $original->end_date;
            $exam->max_score = $examPart + (float) ($allowed['ca']?->max_score ?? 0);
            $exam->exam_type_id = $original->exam_type_id;
            $exam->school_year_id = $allowed['school_year_id'];
            $exam->save();

            $result['created']->push($exam);
        }

        return $result;
    }

    private function enabled(string $branchId): bool
    {
        $value = SchoolBranchSetting::where('school_branch_id', $branchId)
            ->whereHas('settingDefination', fn($query) => $query->where('key', self::ENABLED))
            ->first()?->value;

        // On by default, like the setting.
        return $value === null || filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private function describe(SchoolSemester $semester): array
    {
        $specialty = $semester->schoolYear?->specialty;

        return [
            'school_semester_id' => $semester->id,
            'specialty_id' => $specialty?->id,
            'specialty' => $specialty?->specialty_name,
            'level' => $specialty?->level?->name,
        ];
    }

    private function date(mixed $value): string
    {
        return substr((string) ($value instanceof \DateTimeInterface ? $value->format('Y-m-d') : $value), 0, 10);
    }
}
