<?php

namespace App\Jobs\GradeScale;

use App\Jobs\Import\BaseImportJob;
use App\Models\GradeScale\SchoolGradeScale;
use App\Models\GradeScale\SchoolGradeScaleCategory;
use App\Services\Grade\GradeSchemeSettings;
use App\Services\Import\ImportLookups;
use App\Services\Import\ImportRowException;
use App\Services\Import\ImportType;

/**
 * Fills grade scales from a spreadsheet. One row is one grade of a scale:
 *
 *   grade_title     the scale, e.g. "Level One Exam" (its exam type is optional)
 *   grade_max_score the score the scale is marked out of, e.g. 100
 *   grade           the letter grade, e.g. A, B+
 *   min_score / max_score, grade_points, result, resit_result, performance
 *
 * The rules are those of the grade scale form (GradeScaleService): a letter
 * appears once per scale, score ranges do not overlap, and all grades of a
 * scale share its maximum score. Rows of a scale are checked against the
 * grades it already has and against the rows above them.
 */
class GradeScaleImportJob extends BaseImportJob
{
    private const RESULTS = [
        'passed' => ['passed', 'pass', 'p'],
        'failed' => ['failed', 'fail', 'f'],
    ];

    private const RESIT_RESULTS = [
        'no_resit' => ['no resit', 'no_resit', 'none', 'no'],
        'resit' => ['resit', 'yes'],
        'high_resit_potential' => ['high resit potential', 'high_resit_potential', 'high'],
        'low_resit_potential' => ['low resit potential', 'low_resit_potential', 'low'],
    ];

    /** @var array<string, float> scale id => maximum score given by the first row of the scale */
    private array $scaleMaxScores = [];

    public static function importType(): ImportType
    {
        return ImportType::GRADE_SCALE;
    }

    protected function requiredColumns(): array
    {
        return [
            'grade_title' => 'Grade Title',
            'grade_max_score' => 'Grade Max Score',
            'grade' => 'Grade',
            'min_score' => 'Min Score',
            'max_score' => 'Max Score',
            'grade_points' => 'Grade Points',
            'result' => 'Result',
            'resit_result' => 'Resit Result',
            'performance' => 'Performance',
        ];
    }

    protected function rules(): array
    {
        $score = ['required', 'numeric', 'min:0', 'max:1000', 'decimal:0,2'];

        return [
            'category_id' => ['required'],
            'grade_max_score' => $score,
            'letter_grade_id' => ['required'],
            'minimum_score' => $score,
            'maximum_score' => [...$score, 'gte:minimum_score', 'lte:grade_max_score'],
            // The upper bound is the school's Maximum Attainable GPA, checked in importRow().
            'grade_points' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'result' => ['required'],
            'resit_result' => ['required'],
            'performance' => ['required', 'string', 'max:150'],
        ];
    }

    protected function attributes(): array
    {
        return [
            'category_id' => 'grade title',
            'grade_max_score' => 'grade max score',
            'letter_grade_id' => 'grade',
            'minimum_score' => 'min score',
            'maximum_score' => 'max score',
            'grade_points' => 'grade points',
            'resit_result' => 'resit result',
        ];
    }

    protected function messages(): array
    {
        return [
            'maximum_score.gte' => 'The max score must be at least the min score.',
            'maximum_score.lte' => 'The max score cannot be higher than the grade max score.',
        ];
    }

    protected function normalize(array $row): array
    {
        $category = $this->category($row['grade_title'] ?? null, $row['exam_type'] ?? null);

        $letter = $this->lookups->reference('letter_grades', ['letter_grade'], $row['grade'] ?? null);
        if (!empty($row['grade']) && !$letter) {
            throw ImportRowException::notFound('Letter grade not found. Use letter grades such as A, B+, C.', 'grade', $row['grade']);
        }

        return [
            'grade_title' => $row['grade_title'] ?? null,
            'category_id' => $category?->id,
            'grade_max_score' => $row['grade_max_score'] ?? null,
            'grade' => $row['grade'] ?? null,
            'letter_grade_id' => $letter?->id,
            'minimum_score' => $row['min_score'] ?? null,
            'maximum_score' => $row['max_score'] ?? null,
            'grade_points' => $row['grade_points'] ?? null,
            'result' => $this->option($row['result'] ?? null, self::RESULTS, 'result', 'Result must be passed or failed.'),
            'resit_result' => $this->option($row['resit_result'] ?? null, self::RESIT_RESULTS, 'resit_result', 'Resit result must be no resit, resit, high resit potential or low resit potential.'),
            'performance' => $row['performance'] ?? null,
        ];
    }

    protected function duplicateKey(array $row): ?string
    {
        return "{$row['category_id']}|{$row['letter_grade_id']}";
    }

    protected function duplicateField(): ?string
    {
        return 'grade';
    }

    protected function importRow(array $row, int $rowNumber): string
    {
        $scheme = app(GradeSchemeSettings::class);
        if (!$scheme->isLetterGradeAllowed($this->schoolBranchId, $row['letter_grade_id'])) {
            throw ImportRowException::notAllowed(
                'This letter grade is not part of your Defined Letter Grade Scheme (Settings › Grade Settings).',
                'grade',
                $row['grade']
            );
        }
        $maxGpa = $scheme->maxGpa($this->schoolBranchId);
        if ((float) $row['grade_points'] > $maxGpa + 0.00001) {
            throw ImportRowException::invalid(
                "Grade points cannot exceed your Maximum Attainable GPA of {$scheme->format($maxGpa)}.",
                'grade_points',
                $row['grade_points']
            );
        }

        $category = SchoolGradeScaleCategory::where('school_branch_id', $this->schoolBranchId)
            ->lockForUpdate()
            ->findOrFail($row['category_id']);
        $maxScore = (float) $row['grade_max_score'];

        // All grades of a scale are marked out of the same score.
        $expected = $this->scaleMaxScores[$category->id] ?? null;
        if ($expected === null && $category->schoolGradeScale()->exists() && $category->max_score !== null) {
            $expected = (float) $category->max_score;
        }
        if ($expected !== null && abs($expected - $maxScore) > 0.001) {
            throw ImportRowException::invalid(
                'The grade max score differs from the one of this grade scale. Every grade of a scale must use the same max score.',
                'grade_max_score',
                $row['grade_max_score']
            );
        }

        $grades = SchoolGradeScale::where('school_branch_id', $this->schoolBranchId)
            ->where('grades_category_id', $category->id)
            ->get(['letter_grade_id', 'minimum_score', 'maximum_score']);

        if ($grades->contains('letter_grade_id', $row['letter_grade_id'])) {
            throw ImportRowException::alreadyExists('This grade already exists in this grade scale.', 'grade', $row['grade']);
        }

        $min = (float) $row['minimum_score'];
        $max = (float) $row['maximum_score'];
        $overlaps = $grades->contains(fn($grade) => $min <= (float) $grade->maximum_score && $max >= (float) $grade->minimum_score);

        if ($overlaps) {
            throw ImportRowException::notAllowed(
                'The score range overlaps another grade of this grade scale. Ranges must not overlap.',
                'min_score',
                "{$row['minimum_score']}–{$row['maximum_score']}"
            );
        }

        SchoolGradeScale::create([
            'school_branch_id' => $this->schoolBranchId,
            'grades_category_id' => $category->id,
            'letter_grade_id' => $row['letter_grade_id'],
            'minimum_score' => $min,
            'maximum_score' => $max,
            'grade_points' => (float) $row['grade_points'],
            'result' => $row['result'],
            'resit_result' => $row['resit_result'],
            'performance' => $row['performance'],
        ]);

        $category->max_score = $maxScore;
        $category->save();
        $this->scaleMaxScores[$category->id] = $maxScore;

        return self::OUTCOME_CREATED;
    }

    /** The school's grade scale for a title such as "Level One Exam". */
    private function category(?string $title, ?string $examType): ?SchoolGradeScaleCategory
    {
        if ($title === null) {
            return null;
        }

        $system = $this->lookups->reference('grade_scale_categories', ['title'], $title);
        if (!$system) {
            throw ImportRowException::notFound(
                'Grade scale not found. Use a grade scale title such as ' . $this->lookups->referenceNames('grade_scale_categories', 'title', limit: 3) . '.',
                'grade_title',
                $title
            );
        }

        if ($examType !== null && ImportLookups::key($examType) !== ImportLookups::key($system->exam_type)) {
            throw ImportRowException::invalid('The exam type does not match this grade scale\'s exam type.', 'exam_type', $examType);
        }

        $category = SchoolGradeScaleCategory::where('school_branch_id', $this->schoolBranchId)
            ->where('grades_category_id', $system->id)
            ->first();

        if (!$category) {
            throw ImportRowException::notFound('Your school does not have this grade scale set up yet.', 'grade_title', $title);
        }

        return $category;
    }

    private function option(?string $value, array $options, string $field, string $message): ?string
    {
        if ($value === null) {
            return null;
        }

        $key = str_replace('_', ' ', ImportLookups::key($value));
        foreach ($options as $canonical => $aliases) {
            if (in_array($key, array_map(fn($alias) => str_replace('_', ' ', $alias), $aliases), true)) {
                return $canonical;
            }
        }

        throw ImportRowException::invalid($message, $field, $value);
    }
}
