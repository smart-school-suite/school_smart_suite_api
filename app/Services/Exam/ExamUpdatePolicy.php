<?php

namespace App\Services\Exam;

use App\Models\AcademicYear\SchoolAcademicYear;
use App\Models\Exam\Exam;
use App\Models\Exam\ExamCandidate;
use App\Models\Exam\ExamResultPublication;
use App\Models\Exam\ExamScore;
use App\Models\Examtype;
use App\Models\ExamTimetable\ExamTimetableSlot;
use App\Models\Promotion\PromotionRun;
use App\Models\SchoolSemester;
use App\Models\StudentResults;
use App\Services\Helpers\DateRules\DateRangeRules;

/**
 * What may change on an exam, and within which limits. The update endpoints
 * enforce it and the update drawer shows it, so both agree.
 *
 * - An exam whose results are published, whose academic year has ended or
 *   is promoting its students, is read-only.
 * - Its academic year never changes: its candidates, courses and timetable
 *   come from that academic year's specialty. Delete the exam and create it
 *   in the other academic year instead.
 * - Its exam type changes only before it starts, before a timetable or
 *   scores exist, and (for a CA) while no semester exam includes its score.
 *   It stays unique within the academic year, and a semester exam needs its
 *   semester's CA.
 * - Its start date changes only before it starts; its end date until it
 *   ends. Dates never move into the past, stay within its semester (or the
 *   academic year when the semester isn't set up), and keep every timetabled
 *   paper inside them.
 * - Its max score changes only before any candidate is evaluated, and for a
 *   CA only while no semester exam that includes it is evaluated.
 */
class ExamUpdatePolicy
{
    use DateRangeRules;

    private const PROMOTION_STARTED = ['evaluating', 'processing', 'completed', 'completed_with_issues', 'scheduled'];

    /** The rules for the exam as it is now, for the update drawer. */
    public function describe(Exam $exam): array
    {
        $exam->loadMissing(['examType', 'schoolYear.systemAcademicYear', 'schoolYear.specialty.level']);
        $year = $exam->schoolYear;
        $context = $this->context($exam);
        $status = $this->status($exam);
        $readOnly = $this->readOnlyReasons($exam, $context);

        $startReasons = $readOnly ?: ($status !== 'upcoming'
            ? ["The exam began on {$this->human($exam->start_date)}. A start date that has passed cannot change."]
            : []);
        $endReasons = $readOnly ?: ($status === 'ended'
            ? ["The exam ended on {$this->human($exam->end_date)}. Its dates are history and cannot change."]
            : []);
        $typeReasons = $readOnly ?: $this->examTypeReasons($exam, $context, $status);
        $scoreReasons = $readOnly ?: $this->maxScoreReasons($exam, $context);
        $window = $context['window'];

        return [
            'id' => $exam->id,
            'status' => $status,
            'read_only' => !empty($readOnly),
            'exam_type' => [
                'id' => $exam->examType?->id,
                'name' => $exam->examType?->exam_name,
                'type' => $exam->examType?->type,
                'semester' => $exam->examType?->semester,
            ],
            'academic_year' => [
                'id' => $year?->id,
                'name' => $year?->systemAcademicYear?->name,
                'start_date' => $this->date($year?->start_date),
                'end_date' => $this->date($year?->end_date),
                'specialty' => $year?->specialty?->specialty_name,
                'level' => $year?->specialty?->level?->name,
            ],
            // Where the exam's dates must fall: its semester, or the academic year.
            'window' => $window,
            'start_date' => $this->date($exam->start_date),
            'end_date' => $this->date($exam->end_date),
            'max_score' => (float) $exam->max_score,
            // A semester exam's max score includes its CA's; the form edits the exam's own part.
            'ca_max_score' => $context['ca_max_score'],
            'exam_portion' => round((float) $exam->max_score - $context['ca_max_score'], 2),
            'fields' => [
                'start_date' => [
                    'editable' => empty($startReasons),
                    'reasons' => $startReasons,
                    ...$this->startBounds($window, $context, $this->date($exam->end_date)),
                ],
                'end_date' => [
                    'editable' => empty($endReasons),
                    'reasons' => $endReasons,
                    ...$this->endBounds($window, $context, $this->date($exam->start_date)),
                ],
                'max_score' => [
                    'editable' => empty($scoreReasons),
                    'reasons' => $scoreReasons,
                    'min' => 0,
                    'max' => round(999.99 - $context['ca_max_score'], 2),
                ],
                'exam_type_id' => [
                    'editable' => empty($typeReasons),
                    'reasons' => $typeReasons,
                    'options' => empty($typeReasons) ? $this->examTypeOptions($exam) : [],
                ],
                'school_year_id' => [
                    'editable' => false,
                    'reasons' => [$this->academicYearReason($status)],
                ],
            ],
            'dependents' => $context['counts'],
        ];
    }

    /**
     * Checks the requested date, exam type and academic year changes against
     * the rules and returns the ones that actually change. A max score (the
     * exam's own part) is checked for whether it may change at all; the
     * service combines it with the CA. With $allowNoChanges a request that changes nothing returns []
     * instead of being refused, for bulk updates.
     */
    public function validatedChanges(Exam $exam, array $data, bool $allowNoChanges = false): array
    {
        $exam->loadMissing(['examType', 'schoolYear']);
        $context = $this->context($exam);
        $status = $this->status($exam);

        $changes = collect([
            'start_date' => isset($data['start_date']) ? substr((string) $data['start_date'], 0, 10) : null,
            'end_date' => isset($data['end_date']) ? substr((string) $data['end_date'], 0, 10) : null,
            'exam_type_id' => $data['exam_type_id'] ?? null,
            'school_year_id' => $data['school_year_id'] ?? null,
            'max_score' => isset($data['max_score']) && $data['max_score'] !== '' ? (float) $data['max_score'] : null,
        ])
            ->filter(fn($value) => $value !== null && $value !== '')
            ->reject(fn($value, $field) => match ($field) {
                'start_date', 'end_date' => $value === $this->date($exam->{$field}),
                'max_score' => abs($value - ((float) $exam->max_score - $context['ca_max_score'])) < 0.0001,
                default => $value === $exam->{$field},
            })
            ->all();

        if (empty($changes)) {
            if ($allowNoChanges) {
                return [];
            }
            $this->fail('No Changes', 'Change at least one field before saving.');
        }

        if ($readOnly = $this->readOnlyReasons($exam, $context)) {
            $this->fail('Exam Is Read-Only', $readOnly[0], 409);
        }

        if (isset($changes['school_year_id'])) {
            $this->fail('Academic Year Cannot Change', $this->academicYearReason($status), 409);
        }

        if (isset($changes['max_score']) && ($reasons = $this->maxScoreReasons($exam, $context))) {
            $this->fail('Max Score Cannot Change', $reasons[0], 409);
        }

        $window = $context['window'];
        if (isset($changes['exam_type_id'])) {
            if ($reasons = $this->examTypeReasons($exam, $context, $status)) {
                $this->fail('Exam Type Cannot Change', $reasons[0], 409);
            }
            $type = Examtype::find($changes['exam_type_id']);
            if (!$type) {
                $this->fail('Exam Type Not Found', 'The selected exam type does not exist.', 404);
            }
            if ($reason = $this->examTypeUnavailable($exam, $type)) {
                $this->fail('Exam Type Unavailable', $reason, 409);
            }
            // The dates must then fit the new type's semester.
            $window = $this->window($exam->schoolYear, $type);
        }

        if (isset($changes['start_date']) && $status !== 'upcoming') {
            $this->fail('Start Date Cannot Change', "The exam began on {$this->human($exam->start_date)}. A start date that has passed cannot change.", 409);
        }
        if (isset($changes['end_date']) && $status === 'ended') {
            $this->fail('End Date Cannot Change', "The exam ended on {$this->human($exam->end_date)}. Its dates are history and cannot change.", 409);
        }

        $start = $changes['start_date'] ?? $this->date($exam->start_date);
        $end = $changes['end_date'] ?? $this->date($exam->end_date);

        if ($start > $end) {
            $this->fail('Invalid Date Range', 'The start date must be on or before the end date.');
        }
        if (isset($changes['start_date']) || isset($changes['exam_type_id'])) {
            $this->assertWithin('start date', $start, $this->startBounds($window, $context, $end, isset($changes['start_date'])));
        }
        if (isset($changes['end_date']) || isset($changes['exam_type_id'])) {
            $this->assertWithin('end date', $end, $this->endBounds($window, $context, $start, isset($changes['end_date'])));
        }

        unset($changes['school_year_id']);

        return $changes;
    }

    private function readOnlyReasons(Exam $exam, array $context): array
    {
        $reasons = [];
        if ($context['published']) {
            $reasons[] = 'Its results have been published to students, so the exam can no longer change.';
        }
        $yearEnd = $this->date($exam->schoolYear?->end_date);
        if ($yearEnd && $yearEnd < $this->today()) {
            $reasons[] = "Its academic year ended on {$this->human($yearEnd)}, so the exam can no longer change.";
        }
        if ($context['promotion_started']) {
            $reasons[] = 'Students are being or have been promoted out of its academic year, so the exam can no longer change.';
        }
        return $reasons;
    }

    private function examTypeReasons(Exam $exam, array $context, string $status): array
    {
        $reasons = [];
        if ($status !== 'upcoming') {
            $reasons[] = 'The exam has already started, so it stays the same exam type.';
        }
        $counts = $context['counts'];
        $linked = array_filter([
            $counts['scores'] ? $this->plural($counts['scores'], 'recorded score') : null,
            $counts['timetable_slots'] ? $this->plural($counts['timetable_slots'], 'timetabled paper') : null,
        ]);
        if ($linked) {
            $reasons[] = 'It already has ' . implode(' and ', $linked) . '. Delete and recreate the exam to change its type.';
        }
        if ($context['is_ca'] && $context['dependents']->isNotEmpty()) {
            $reasons[] = 'Its score is part of ' . $context['dependents']->map(fn($e) => $e->examType?->exam_name)->filter()->implode(', ') . '. Remove that exam first.';
        }
        return $reasons;
    }

    private function maxScoreReasons(Exam $exam, array $context): array
    {
        $reasons = [];
        if ($context['evaluated']) {
            $reasons[] = 'Candidates have already been evaluated, so the max score can no longer change.';
        }
        if ($context['is_ca'] && $context['evaluated_dependents']) {
            $reasons[] = 'This CA is part of ' . implode(', ', $context['evaluated_dependents']) . ', which already has recorded scores.';
        }
        return $reasons;
    }

    private function academicYearReason(string $status): string
    {
        return $status === 'upcoming'
            ? "Its candidates, courses and timetable come from this academic year's specialty. To use another academic year, delete this exam and create it there."
            : "Its candidates, courses and timetable come from this academic year's specialty, so it never changes.";
    }

    /** Why the exam can't become this exam type, or null. */
    private function examTypeUnavailable(Exam $exam, Examtype $type): ?string
    {
        $taken = Exam::where('school_branch_id', $exam->school_branch_id)
            ->where('school_year_id', $exam->school_year_id)
            ->where('exam_type_id', $type->id)
            ->where('id', '!=', $exam->id)
            ->exists();
        if ($taken) {
            return "This academic year already has a {$type->exam_name}.";
        }
        if (strtolower((string) $type->type) !== 'ca' && !$this->caFor($exam, $type)) {
            return "A {$type->exam_name} needs its semester's CA. Create the {$type->semester} semester CA first.";
        }
        return null;
    }

    private function examTypeOptions(Exam $exam): array
    {
        return Examtype::orderBy('exam_name')->get()
            ->reject(fn(Examtype $type) => $type->id === $exam->exam_type_id || $this->examTypeUnavailable($exam, $type))
            ->map(fn(Examtype $type) => [
                'id' => $type->id,
                'exam_name' => $type->exam_name,
                'type' => $type->type,
                'semester' => $type->semester,
                // The dates must fit this type's semester; the drawer re-derives the ranges.
                'window' => $this->window($exam->schoolYear, $type),
            ])
            ->values()
            ->all();
    }

    private function startBounds(array $window, array $context, string $end, bool $notInPast = true): array
    {
        $min = $notInPast ? [['date' => $this->today(), 'why' => 'it cannot be in the past']] : [];
        $max = [['date' => $end, 'why' => 'it must be on or before the end date', 'kind' => 'pair']];

        if ($window['start_date']) {
            $min[] = ['date' => $window['start_date'], 'why' => $window['label'] . ' starts on ' . $this->human($window['start_date']), 'kind' => 'window'];
        }
        if ($window['end_date']) {
            $max[] = ['date' => $window['end_date'], 'why' => $window['label'] . ' ends on ' . $this->human($window['end_date']), 'kind' => 'window'];
        }
        if ($context['first_slot']) {
            $max[] = ['date' => $context['first_slot'], 'why' => 'the timetable has a paper on ' . $this->human($context['first_slot'])];
        }

        return $this->bounds($min, $max);
    }

    private function endBounds(array $window, array $context, string $start, bool $notInPast = true): array
    {
        $min = [['date' => $start, 'why' => 'it must be on or after the start date', 'kind' => 'pair']];
        if ($notInPast) {
            $min[] = ['date' => $this->today(), 'why' => 'it cannot be in the past'];
        }
        $max = [];

        if ($window['end_date']) {
            $max[] = ['date' => $window['end_date'], 'why' => $window['label'] . ' ends on ' . $this->human($window['end_date']), 'kind' => 'window'];
        }
        if ($context['last_slot']) {
            $min[] = ['date' => $context['last_slot'], 'why' => 'the timetable has a paper on ' . $this->human($context['last_slot'])];
        }

        return $this->bounds($min, $max);
    }

    /** The dates an exam of this type must fall within: its semester, else the academic year. */
    private function window(?SchoolAcademicYear $year, ?Examtype $type): array
    {
        $semester = $type?->semester_id && $year
            ? SchoolSemester::where('school_year_id', $year->id)->where('semester_id', $type->semester_id)->with('semester:id,name')->first()
            : null;

        return $semester
            ? ['label' => "the {$semester->semester?->name}", 'source' => 'semester', 'start_date' => $this->date($semester->start_date), 'end_date' => $this->date($semester->end_date)]
            : ['label' => 'the academic year', 'source' => 'academic_year', 'start_date' => $this->date($year?->start_date), 'end_date' => $this->date($year?->end_date)];
    }

    private function caFor(Exam $exam, Examtype $type): ?Exam
    {
        return Exam::where('school_branch_id', $exam->school_branch_id)
            ->where('school_year_id', $exam->school_year_id)
            ->where('id', '!=', $exam->id)
            ->whereHas('examType', fn($query) => $query->where('semester', $type->semester)->where('type', 'ca'))
            ->first();
    }

    /** What depends on the exam. */
    private function context(Exam $exam): array
    {
        $isCa = strtolower((string) $exam->examType?->type) === 'ca';
        $dependents = $isCa
            ? Exam::where('school_branch_id', $exam->school_branch_id)
                ->where('school_year_id', $exam->school_year_id)
                ->where('id', '!=', $exam->id)
                ->whereHas('examType', fn($query) => $query->where('semester', $exam->examType->semester)->where('type', '!=', 'ca'))
                ->with('examType')
                ->get()
            : collect();
        $ca = !$isCa && $exam->examType ? $this->caFor($exam, $exam->examType) : null;
        $evaluatedIds = $this->evaluatedExamIds(array_merge([$exam->id], $dependents->pluck('id')->all()));
        $slots = ExamTimetableSlot::where('exam_id', $exam->id);

        return [
            'is_ca' => $isCa,
            'dependents' => $dependents,
            'ca_max_score' => $ca ? (float) $ca->max_score : 0.0,
            'evaluated' => in_array($exam->id, $evaluatedIds, true),
            'evaluated_dependents' => $dependents->whereIn('id', $evaluatedIds)->map(fn($e) => $e->examType?->exam_name)->filter()->values()->all(),
            'window' => $this->window($exam->schoolYear, $exam->examType),
            'first_slot' => $this->date((clone $slots)->min('date')),
            'last_slot' => $this->date((clone $slots)->max('date')),
            'published' => ExamResultPublication::where('exam_id', $exam->id)->exists(),
            'promotion_started' => PromotionRun::where('school_year_id', $exam->school_year_id)->whereIn('status', self::PROMOTION_STARTED)->exists(),
            'counts' => [
                'candidates' => ExamCandidate::where('exam_id', $exam->id)->count(),
                'scores' => ExamScore::where('exam_id', $exam->id)->count(),
                'timetable_slots' => (clone $slots)->count(),
                'grade_scale' => (bool) $exam->grades_category_id,
                'linked_exams' => $dependents->count(),
            ],
        ];
    }

    /** Exams (from the given ids) with recorded scores or computed results. */
    private function evaluatedExamIds(array $ids): array
    {
        return ExamScore::whereIn('exam_id', $ids)->distinct()->pluck('exam_id')
            ->merge(StudentResults::whereIn('exam_id', $ids)->distinct()->pluck('exam_id'))
            ->unique()->values()->all();
    }

    private function status(Exam $exam): string
    {
        $today = $this->today();
        return match (true) {
            $this->date($exam->end_date) < $today => 'ended',
            $this->date($exam->start_date) <= $today => 'ongoing',
            default => 'upcoming',
        };
    }
}
