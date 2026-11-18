<?php

namespace App\Services\AcademicYear;

use App\Exceptions\AppException;
use App\Models\AcademicYear\SchoolAcademicYear;
use App\Models\AcademicYear\SystemAcademicYear;
use App\Models\Exam\Exam;
use App\Models\FeeSchedule;
use App\Models\FeeScheduleSlot;
use App\Models\Promotion\PromotionRun;
use App\Models\ResitExam;
use App\Models\SchoolSemester;
use App\Models\Student;
use App\Models\TuitionFees;
use App\Models\TuitionFeeTransactions;
use Carbon\Carbon;

/**
 * What may change on a school academic year, and within which limits. The
 * update endpoint enforces it and the update drawer shows it, so both always
 * agree.
 *
 * - An academic year that has ended, or whose students are being or have
 *   been promoted, is read-only.
 * - Its specialty never changes: semesters, exams, the fee schedule and
 *   tuition records belong to it. Delete the year (possible until it starts)
 *   and create one for the right specialty instead.
 * - Its system academic year (e.g. 2026/2027) changes only before it starts
 *   and before any semester, exam or resit is created for it.
 * - Its start date changes only before it starts; its end date until it ends.
 *   Dates never move into the past, stay within the system academic year,
 *   keep clear of the specialty's other academic years, and keep every
 *   semester, exam, resit and fee installment inside the academic year.
 */
class SchoolAcademicYearUpdatePolicy
{
    private const PROMOTION_STARTED = ['evaluating', 'processing', 'completed', 'completed_with_issues', 'scheduled'];

    /** The rules for the year as it is now, for the update drawer. */
    public function describe(SchoolAcademicYear $year): array
    {
        $year->loadMissing(['systemAcademicYear', 'specialty.level']);
        $context = $this->context($year);
        $today = $this->today();
        $status = $this->status($year, $today);
        $readOnly = $this->readOnlyReasons($year, $context, $status);

        $startReasons = $readOnly ?: ($status !== 'upcoming'
            ? ["The academic year began on {$this->human($year->start_date)}. A start date that has passed cannot change."]
            : []);
        $endReasons = $readOnly;
        $systemYearReasons = $readOnly ?: $this->systemYearReasons($year, $context, $status);

        $startBounds = $this->startBounds($year, $year->systemAcademicYear, $context, $this->date($year->end_date));
        $endBounds = $this->endBounds($year, $year->systemAcademicYear, $context, $this->date($year->start_date));

        return [
            'id' => $year->id,
            'status' => $status,
            'read_only' => !empty($readOnly),
            'specialty' => [
                'id' => $year->specialty_id,
                'name' => $year->specialty?->specialty_name,
                'level' => $year->specialty?->level?->name,
            ],
            'system_academic_year' => $year->systemAcademicYear?->only(['id', 'name', 'year_start', 'year_end']),
            'start_date' => $this->date($year->start_date),
            'end_date' => $this->date($year->end_date),
            'fields' => [
                'start_date' => ['editable' => empty($startReasons), 'reasons' => $startReasons, ...$startBounds],
                'end_date' => ['editable' => empty($endReasons), 'reasons' => $endReasons, ...$endBounds],
                'system_academic_year_id' => [
                    'editable' => empty($systemYearReasons),
                    'reasons' => $systemYearReasons,
                    'options' => empty($systemYearReasons) ? $this->systemYearOptions($year) : [],
                ],
                'specialty_id' => [
                    'editable' => false,
                    'reasons' => [$this->specialtyReason($status)],
                ],
            ],
            'dependents' => $context['counts'],
        ];
    }

    /**
     * Checks the requested changes against the rules. Returns the values to
     * save (only the fields that actually change).
     */
    public function validatedChanges(SchoolAcademicYear $year, array $data): array
    {
        $year->loadMissing('systemAcademicYear');
        $context = $this->context($year);
        $status = $this->status($year, $this->today());

        $changes = collect([
            'start_date' => isset($data['start_date']) ? substr((string) $data['start_date'], 0, 10) : null,
            'end_date' => isset($data['end_date']) ? substr((string) $data['end_date'], 0, 10) : null,
            'system_academic_year_id' => $data['system_academic_year_id'] ?? null,
            'specialty_id' => $data['specialty_id'] ?? null,
        ])
            ->filter(fn($value) => $value !== null && $value !== '')
            ->reject(fn($value, $field) => $value === ($field === 'start_date' || $field === 'end_date'
                ? $this->date($year->{$field})
                : $year->{$field}))
            ->all();

        if (empty($changes)) {
            throw new AppException('Nothing to update.', 422, 'No Changes', 'Change at least one field before saving.');
        }

        if ($readOnly = $this->readOnlyReasons($year, $context, $status)) {
            $this->fail('Academic Year Is Read-Only', $readOnly[0], 409);
        }

        if (isset($changes['specialty_id'])) {
            $this->fail('Specialty Cannot Change', $this->specialtyReason($status), 409);
        }

        if (isset($changes['system_academic_year_id']) && ($reasons = $this->systemYearReasons($year, $context, $status))) {
            $this->fail('Academic Year Cannot Change', $reasons[0], 409);
        }

        if (isset($changes['start_date']) && $status !== 'upcoming') {
            $this->fail('Start Date Cannot Change', "The academic year began on {$this->human($year->start_date)}. A start date that has passed cannot change.", 409);
        }

        $systemYear = isset($changes['system_academic_year_id'])
            ? SystemAcademicYear::find($changes['system_academic_year_id'])
            : $year->systemAcademicYear;

        if (!$systemYear) {
            $this->fail('System Academic Year Not Found', 'The selected academic year no longer exists.', 404);
        }

        if (isset($changes['system_academic_year_id'])) {
            $taken = SchoolAcademicYear::where('school_branch_id', $year->school_branch_id)
                ->where('specialty_id', $year->specialty_id)
                ->where('system_academic_year_id', $systemYear->id)
                ->where('id', '!=', $year->id)
                ->exists();
            if ($taken) {
                $this->fail('Academic Year Already Exists', "This specialty already has an academic year for {$systemYear->name}.", 409);
            }
        }

        $start = $changes['start_date'] ?? $this->date($year->start_date);
        $end = $changes['end_date'] ?? $this->date($year->end_date);

        if ($start >= $end) {
            $this->fail('Invalid Date Range', 'The start date must be before the end date.');
        }

        // Every limit is checked whenever a field that affects it changes,
        // so a new system academic year also re-checks dates kept as they are.
        $checkStart = isset($changes['start_date']) || isset($changes['system_academic_year_id']);
        $checkEnd = isset($changes['end_date']) || isset($changes['system_academic_year_id']);

        if ($checkStart) {
            $this->assertWithin('start date', $start, $this->startBounds($year, $systemYear, $context, $end));
        }
        if ($checkEnd) {
            $this->assertWithin('end date', $end, $this->endBounds($year, $systemYear, $context, $start));
        }

        unset($changes['specialty_id']);

        return $changes;
    }

    private function readOnlyReasons(SchoolAcademicYear $year, array $context, string $status): array
    {
        $reasons = [];
        if ($status === 'ended') {
            $reasons[] = "This academic year ended on {$this->human($year->end_date)}. Its records are history and cannot change.";
        }
        if ($context['promotion_started']) {
            $reasons[] = 'Students are being or have been promoted out of this academic year, so it can no longer change.';
        }
        return $reasons;
    }

    private function systemYearReasons(SchoolAcademicYear $year, array $context, string $status): array
    {
        $reasons = [];
        if ($status !== 'upcoming') {
            $reasons[] = 'The academic year has already started, so it stays under the same school year.';
        }
        $counts = $context['counts'];
        $linked = array_filter([
            $counts['semesters'] ? $this->plural($counts['semesters'], 'semester') : null,
            $counts['exams'] ? $this->plural($counts['exams'], 'exam') : null,
            $counts['resit_exams'] ? $this->plural($counts['resit_exams'], 'resit exam') : null,
        ]);
        if ($linked) {
            $reasons[] = 'It already has ' . implode(', ', $linked) . ' created under this school year.';
        }
        return $reasons;
    }

    private function specialtyReason(string $status): string
    {
        return $status === 'upcoming'
            ? 'Semesters, exams, the fee schedule and tuition records belong to this specialty. To use another specialty, delete this academic year and create a new one.'
            : 'Semesters, exams, the fee schedule and tuition records belong to this specialty, so it never changes.';
    }

    /** [min, max, limits] for the start date given the other values. */
    private function startBounds(SchoolAcademicYear $year, ?SystemAcademicYear $systemYear, array $context, string $end): array
    {
        $min = [['date' => $this->today(), 'why' => 'it cannot be in the past']];
        $max = [['date' => $this->shift($end, -1), 'why' => 'it must be before the end date', 'kind' => 'pair']];

        if ($systemYear) {
            $min[] = ['date' => "{$systemYear->year_start}-01-01", 'why' => "it must fall within {$systemYear->name}", 'kind' => 'system_year'];
        }
        if ($context['previous_end']) {
            $min[] = ['date' => $this->shift($context['previous_end'], 1), 'why' => "the specialty's previous academic year ends on {$this->human($context['previous_end'])}"];
        }
        if ($context['earliest_dependent']) {
            $max[] = ['date' => $context['earliest_dependent']['date'], 'why' => "{$context['earliest_dependent']['what']} starts on {$this->human($context['earliest_dependent']['date'])}"];
        }

        return $this->bounds($min, $max);
    }

    /** [min, max, limits] for the end date given the other values. */
    private function endBounds(SchoolAcademicYear $year, ?SystemAcademicYear $systemYear, array $context, string $start): array
    {
        $min = [
            ['date' => $this->today(), 'why' => 'it cannot be in the past'],
            ['date' => $this->shift($start, 1), 'why' => 'it must be after the start date', 'kind' => 'pair'],
        ];
        $max = [];

        if ($systemYear) {
            $max[] = ['date' => "{$systemYear->year_end}-12-31", 'why' => "it must fall within {$systemYear->name}", 'kind' => 'system_year'];
        }
        if ($context['next_start']) {
            $max[] = ['date' => $this->shift($context['next_start'], -1), 'why' => "the specialty's next academic year starts on {$this->human($context['next_start'])}"];
        }
        if ($context['latest_dependent']) {
            $min[] = ['date' => $context['latest_dependent']['date'], 'why' => "{$context['latest_dependent']['what']} runs until {$this->human($context['latest_dependent']['date'])}"];
        }

        return $this->bounds($min, $max);
    }

    private function bounds(array $min, array $max): array
    {
        $tightestMin = collect($min)->sortByDesc('date')->first();
        $tightestMax = collect($max)->sortBy('date')->first();

        $limit = fn(array $item) => ['date' => $item['date'], 'reason' => $item['why'], 'kind' => $item['kind'] ?? 'other'];

        return [
            'min' => $tightestMin['date'] ?? null,
            'max' => $tightestMax['date'] ?? null,
            'min_reason' => $tightestMin['why'] ?? null,
            'max_reason' => $tightestMax['why'] ?? null,
            // Every limit, so a client can re-derive the range when the
            // system academic year (kind "system_year") is changed.
            'limits' => ['min' => array_map($limit, $min), 'max' => array_map($limit, $max)],
        ];
    }

    private function assertWithin(string $label, string $value, array $bounds): void
    {
        if ($bounds['min'] && $bounds['max'] && $bounds['min'] > $bounds['max']) {
            $this->fail('No Valid Date', "There is no valid {$label}: {$bounds['min_reason']}, but {$bounds['max_reason']}.");
        }
        if ($bounds['min'] && $value < $bounds['min']) {
            $this->fail('Date Too Early', "The {$label} must be on or after {$this->human($bounds['min'])}: {$bounds['min_reason']}.");
        }
        if ($bounds['max'] && $value > $bounds['max']) {
            $this->fail('Date Too Late', "The {$label} must be on or before {$this->human($bounds['max'])}: {$bounds['max_reason']}.");
        }
    }

    /** What depends on the academic year, and the specialty's neighbouring years. */
    private function context(SchoolAcademicYear $year): array
    {
        $semesters = SchoolSemester::where('school_year_id', $year->id)->get(['start_date', 'end_date']);
        $exams = Exam::where('school_year_id', $year->id)->get(['start_date', 'end_date']);
        $resits = ResitExam::where('school_year_id', $year->id)->get(['start_date', 'end_date']);
        $scheduleIds = FeeSchedule::where('school_year_id', $year->id)->pluck('id');
        $installments = FeeScheduleSlot::whereIn('fee_schedule_id', $scheduleIds)->pluck('due_date');
        $tuitionIds = TuitionFees::where('school_academic_year_id', $year->id)->pluck('id');

        $starts = collect()
            ->merge($semesters->map(fn($s) => ['date' => $this->date($s->start_date), 'what' => 'a semester']))
            ->merge($exams->map(fn($e) => ['date' => $this->date($e->start_date), 'what' => 'an exam']))
            ->merge($resits->map(fn($r) => ['date' => $this->date($r->start_date), 'what' => 'a resit exam']))
            ->merge($installments->map(fn($d) => ['date' => $this->date($d), 'what' => 'a fee installment']))
            ->filter(fn($item) => $item['date']);
        $ends = collect()
            ->merge($semesters->map(fn($s) => ['date' => $this->date($s->end_date), 'what' => 'a semester']))
            ->merge($exams->map(fn($e) => ['date' => $this->date($e->end_date), 'what' => 'an exam']))
            ->merge($resits->map(fn($r) => ['date' => $this->date($r->end_date), 'what' => 'a resit exam']))
            ->merge($installments->map(fn($d) => ['date' => $this->date($d), 'what' => 'a fee installment']))
            ->filter(fn($item) => $item['date']);

        $siblings = SchoolAcademicYear::where('school_branch_id', $year->school_branch_id)
            ->where('specialty_id', $year->specialty_id)
            ->where('id', '!=', $year->id)
            ->get(['start_date', 'end_date']);
        $start = $this->date($year->start_date);

        return [
            'counts' => [
                'semesters' => $semesters->count(),
                'exams' => $exams->count(),
                'resit_exams' => $resits->count(),
                'fee_installments' => $installments->count(),
                'tuition_fees' => $tuitionIds->count(),
                'paid_tuition_fees' => TuitionFeeTransactions::whereIn('tuition_id', $tuitionIds)->distinct()->count('tuition_id'),
                'students' => Student::where('school_academic_year_id', $year->id)->count(),
            ],
            'earliest_dependent' => $starts->sortBy('date')->first(),
            'latest_dependent' => $ends->sortByDesc('date')->first(),
            'previous_end' => $siblings->filter(fn($s) => $this->date($s->start_date) < $start)->map(fn($s) => $this->date($s->end_date))->max(),
            'next_start' => $siblings->filter(fn($s) => $this->date($s->start_date) > $start)->map(fn($s) => $this->date($s->start_date))->min(),
            'promotion_started' => PromotionRun::where('school_year_id', $year->id)->whereIn('status', self::PROMOTION_STARTED)->exists(),
        ];
    }

    private function systemYearOptions(SchoolAcademicYear $year): array
    {
        $taken = SchoolAcademicYear::where('school_branch_id', $year->school_branch_id)
            ->where('specialty_id', $year->specialty_id)
            ->where('id', '!=', $year->id)
            ->pluck('system_academic_year_id');

        return SystemAcademicYear::whereNotIn('id', $taken)
            ->where('year_end', '>=', (int) substr($this->today(), 0, 4))
            ->orderBy('year_start')
            ->get(['id', 'name', 'year_start', 'year_end'])
            ->toArray();
    }

    private function status(SchoolAcademicYear $year, string $today): string
    {
        return match (true) {
            $this->date($year->end_date) < $today => 'ended',
            $this->date($year->start_date) <= $today => 'ongoing',
            default => 'upcoming',
        };
    }

    private function fail(string $title, string $description, int $code = 422): never
    {
        throw new AppException($description, $code, $title, $description);
    }

    private function plural(int $count, string $noun): string
    {
        return $count . ' ' . $noun . ($count === 1 ? '' : 's');
    }

    private function today(): string
    {
        return now()->toDateString();
    }

    /** "1 May 2027", for messages. */
    private function human($date): string
    {
        return Carbon::parse($this->date($date))->format('j M Y');
    }

    private function shift(string $date, int $days): string
    {
        return Carbon::parse($date)->addDays($days)->toDateString();
    }

    private function date($value): ?string
    {
        return $value ? substr((string) ($value instanceof \DateTimeInterface ? $value->format('Y-m-d') : $value), 0, 10) : null;
    }
}
