<?php

namespace App\Schedular\SemesterTimetable\Constraints\Core;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Collection;

class ConstraintContext
{
    private function __construct(private readonly array $parsed) {}

    public static function fromPayload(array $requestPayload): self
    {
        return new self([
            'hard' => self::parseHard($requestPayload['hard_constraints']  ?? [], $requestPayload),
            'soft' => self::parseSoft($requestPayload['soft_constraints'] ?? []),
        ]);
    }

    private static function parseHard(array $hc, array $requestPayload): array
    {
        return [
            'teachers'        => $requestPayload['teachers']                  ?? [],
            'halls'           => $requestPayload['halls']                     ?? [],
            'tBusySlots'      => $requestPayload['teacher_busy_periods']      ?? [],
            'hBusySlots'      => $requestPayload['hall_busy_periods']         ?? [],
            'tCourses'        => $requestPayload['teacher_courses']           ?? [],
            'tPreferredSlots' => $requestPayload['teacher_preferred_periods'] ?? [],

            'opStartTime'     => $hc['operational_hours']['start_time']      ?? '08:00',
            'opEndTime'       => $hc['operational_hours']['end_time']         ?? '17:00',
            'opDays'          => collect($hc['operational_hours']['operational_days'] ?? [])
                ->map(fn($d) => strtolower($d))
                ->values()
                ->all(),
            'opDayExceptions' => self::indexByDay($hc['operational_hours']['day_exceptions'] ?? []),

            'periodDuration'  => (int) ($hc['schedule_period_duration_minutes']['duration_minutes'] ?? 60),
            'pdExceptions'    => self::indexByDay(
                $hc['schedule_period_duration_minutes']['day_exceptions'] ?? [],
                valueKey: 'duration_minutes'
            ),

            'bpStartTime'     => $hc['break_period']['start_time']          ?? null,
            'bpEndTime'       => $hc['break_period']['end_time']            ?? null,
            'noBp'            => $hc['break_period']['no_break_exceptions'] ?? false,
            'bpDayExceptions' => $hc['break_period']['day_exceptions'] ?? [],

            'jointCourses'    => self::indexByDay(
                $hc['required_joint_course_periods'] ?? [],
                valueKey: null,
                groupMultiple: true
            ),
        ];
    }

    private static function parseSoft(array $sc): array
    {
        return [
            'tDailyHours'            => $sc['teacher_daily_hours']            ?? null,
            'tRequestedWindows'      => $sc['teacher_requested_time_windows'] ?? [],
            'dailyPeriods'           => $sc['schedule_periods_per_day']       ?? null,
            'dailyFreePeriods'       => $sc['schedule_free_periods_per_day']  ?? null,
            'requestedFreePeriods'   => $sc['requested_free_periods']         ?? [],
            'requestedAssignments'   => $sc['requested_assignments']          ?? [],
            'hallRequestedWindows'   => $sc['hall_requested_time_windows']    ?? [],
            'courseRequestedWindows' => $sc['course_requested_time_slots']    ?? [],
            'courseDailyFrequency'   => $sc['course_daily_frequency']         ?? null,
        ];
    }

    // ─── Hard accessors ───────────────────────────────────────────────────

    public function teachers(): Collection
    {
        return collect($this->parsed['hard']['teachers']);
    }

    public function teacher(string $teacherId): ?array
    {
        return $this->teachers()->firstWhere('teacher_id', $teacherId);
    }

    public function halls(): Collection
    {
        return collect($this->parsed['hard']['halls']);
    }

    public function hall(string $hallId): ?array
    {
        return $this->halls()->firstWhere('hall_id', $hallId);
    }

    public function tBusySlots(): Collection
    {
        return collect($this->parsed['hard']['tBusySlots']);
    }

    public function tBusySlotsFor(string $teacherId, string $day): Collection
    {
        return $this->tBusySlots()->filter(
            fn($s) => $s['teacher_id'] === $teacherId && strtolower($s['day']) === strtolower($day)
        )->values();
    }

    public function hBusySlots(): Collection
    {
        return collect($this->parsed['hard']['hBusySlots']);
    }

    public function hBusySlotsFor(string $day): Collection
    {
        return $this->hBusySlots()->filter(
            fn($s) => strtolower($s['day']) === strtolower($day)
        )->values();
    }

    public function tCourses(): Collection
    {
        return collect($this->parsed['hard']['tCourses']);
    }

    public function coursesForTeacher(string $teacherId): Collection
    {
        return $this->tCourses()
            ->filter(fn($tc) => $tc['teacher_id'] === $teacherId)
            ->pluck('course_id')
            ->flatten()
            ->values();
    }

    public function teachersForCourse(string $courseId): Collection
    {
        return $this->tCourses()
            ->filter(fn($tc) => in_array($courseId, $tc['course_ids'] ?? []))
            ->pluck('teacher_id')
            ->values();
    }

    public function tPreferredSlots(): Collection
    {
        return collect($this->parsed['hard']['tPreferredSlots']);
    }

    public function tPreferredSlotsFor(string $teacherId, string $day): Collection
    {
        return $this->tPreferredSlots()->filter(
            fn($s) => $s['teacher_id'] === $teacherId && strtolower($s['day']) === strtolower($day)
        )->values();
    }

    public function opDays(): Collection
    {
        return collect($this->parsed['hard']['opDays']);
    }

    /**
     * Returns ['start' => Carbon, 'end' => Carbon]
     */
    public function operationalWindow(string $day): array
    {
        $w = $this->resolveOperationalWindow(strtolower($day));
        return [
            'start' => Carbon::createFromFormat('H:i', $w['start']),
            'end'   => Carbon::createFromFormat('H:i', $w['end']),
        ];
    }

    /**
     * Returns the operational span as a CarbonInterval.
     */
    public function operationalSpan(string $day): CarbonInterval
    {
        $w = $this->operationalWindow($day);
        return $w['start']->diffAsCarbonInterval($w['end']);
    }

    /**
     * Returns the operational span in minutes as an integer.
     */
    public function operationalMinutes(string $day): int
    {
        $w = $this->operationalWindow($day);
        return $w['start']->diffInMinutes($w['end']);
    }

    /**
     * Returns the period duration as a CarbonInterval.
     */
    public function periodDurationInterval(string $day): CarbonInterval
    {
        return CarbonInterval::minutes($this->periodDuration($day));
    }

    public function periodDuration(string $day): int
    {
        return $this->resolvePeriodDuration(strtolower($day));
    }

    /**
     * Returns ['start' => Carbon, 'end' => Carbon] | null
     */
    public function breakWindow(string $day): ?array
    {
        $w = $this->resolveBreakWindow(strtolower($day));

        if ($w === null) {
            return null;
        }

        return [
            'start' => Carbon::createFromFormat('H:i', $w['start']),
            'end'   => Carbon::createFromFormat('H:i', $w['end']),
        ];
    }

    public function jointCourses(string $day): Collection
    {
        return collect($this->parsed['hard']['jointCourses'][strtolower($day)] ?? []);
    }

    public function allJointCourses(): Collection
    {
        return collect($this->parsed['hard']['jointCourses']);
    }

    public function hardRaw(): array
    {
        return $this->parsed['hard'];
    }

    // ─── Soft accessors ───────────────────────────────────────────────────

    public function tDailyHours(): ?array
    {
        return $this->parsed['soft']['tDailyHours'];
    }

    public function tDailyHoursFor(string $teacherId): ?array
    {
        $config = $this->tDailyHours();

        if ($config === null) {
            return null;
        }

        $exception = collect($config['teacher_exceptions'] ?? [])
            ->firstWhere('teacher_id', $teacherId);

        return [
            'min_hours' => $exception['min_hours'] ?? $config['min_hours'] ?? null,
            'max_hours' => $exception['max_hours'] ?? $config['max_hours'] ?? null,
        ];
    }

    public function tRequestedWindows(): Collection
    {
        return collect($this->parsed['soft']['tRequestedWindows']);
    }

    public function tRequestedWindowsFor(string $day): Collection
    {
        return $this->tRequestedWindows()->filter(
            fn($w) => strtolower($w['day']) === strtolower($day)
        )->values();
    }

    public function dailyPeriods(): ?array
    {
        return $this->parsed['soft']['dailyPeriods'];
    }

    public function dailyPeriodsFor(string $day): ?array
    {
        $config = $this->dailyPeriods();

        if ($config === null) {
            return null;
        }

        $exception = collect($config['day_exceptions'] ?? [])
            ->firstWhere('day', strtolower($day));

        return [
            'min_periods' => $exception['min_periods'] ?? $config['min_periods'] ?? null,
            'max_periods' => $exception['max_periods'] ?? $config['max_periods'] ?? null,
        ];
    }

    public function dailyFreePeriods(): ?array
    {
        return $this->parsed['soft']['dailyFreePeriods'];
    }

    public function dailyFreePeriodsFor(string $day): ?array
    {
        $config = $this->dailyFreePeriods();

        if ($config === null) {
            return null;
        }

        $exception = collect($config['day_exceptions'] ?? [])
            ->firstWhere('day', strtolower($day));

        return [
            'min_free_periods' => $exception['min_free_periods'] ?? $config['min_free_periods'] ?? null,
            'max_free_periods' => $exception['max_free_periods'] ?? $config['max_free_periods'] ?? null,
        ];
    }

    public function requestedFreePeriods(): Collection
    {
        return collect($this->parsed['soft']['requestedFreePeriods']);
    }

    public function requestedFreePeriodsFor(string $day): Collection
    {
        return $this->requestedFreePeriods()->filter(
            fn($p) => strtolower($p['day']) === strtolower($day)
        )->values();
    }

    public function requestedAssignments(): Collection
    {
        return collect($this->parsed['soft']['requestedAssignments']);
    }

    public function requestedAssignmentsFor(string $day): Collection
    {
        return $this->requestedAssignments()->filter(
            fn($a) => strtolower($a['day']) === strtolower($day)
        )->values();
    }

    public function hRequestedWindows(): Collection
    {
        return collect($this->parsed['soft']['hallRequestedWindows']);
    }

    public function hRequestedWindowsFor(string $day): Collection
    {
        return $this->hRequestedWindows()->filter(
            fn($w) => strtolower($w['day']) === strtolower($day)
        )->values();
    }

    public function cRequestedWindows(): Collection
    {
        return collect($this->parsed['soft']['courseRequestedWindows']);
    }

    public function cRequestedWindowsFor(string $day): Collection
    {
        return $this->cRequestedWindows()->filter(
            fn($w) =>  strtolower($w['day']) === strtolower($day)
        )->values();
    }

    public function cDailyFrequency(): ?array
    {
        return $this->parsed['soft']['courseDailyFrequency'];
    }

    public function cDailyFrequencyFor(string $courseId): ?array
    {
        $config = $this->cDailyFrequency();

        if ($config === null) {
            return null;
        }

        $exception = collect($config['course_exceptions'] ?? [])
            ->firstWhere('course_id', $courseId);

        return [
            'min_frequency' => $exception['min_frequency'] ?? $config['min_frequency'] ?? null,
            'max_frequency' => $exception['max_frequency'] ?? $config['max_frequency'] ?? null,
        ];
    }

    // ─── Overlap helper ───────────────────────────────────────────────────

    /**
     * Checks if two Carbon time ranges overlap.
     */
    public static function overlaps(Carbon $aStart, Carbon $aEnd, Carbon $bStart, Carbon $bEnd): bool
    {
        return $aStart->lessThan($bEnd) && $aEnd->greaterThan($bStart);
    }

    // ─── Internal resolvers ───────────────────────────────────────────────

    private function resolveOperationalWindow(string $day): array
    {
        $hard = $this->parsed['hard'];

        if (isset($hard['opDayExceptions'][$day])) {
            $ex = $hard['opDayExceptions'][$day];
            return ['start' => $ex['start_time'], 'end' => $ex['end_time']];
        }

        return ['start' => $hard['opStartTime'], 'end' => $hard['opEndTime']];
    }

    private function resolvePeriodDuration(string $day): int
    {
        $hard = $this->parsed['hard'];
        return isset($hard['pdExceptions'][$day])
            ? (int) $hard['pdExceptions'][$day]
            : (int) $hard['periodDuration'];
    }

    private function resolveBreakWindow(string $day): ?array
    {
        $hard = $this->parsed['hard'];
        $bpDayExceptions = collect($hard["bpDayExceptions"] ?? []);
        $day = strtolower($day);

        if ($this->dayHasNoBreak($day)) {
            return null;
        }

        if ($bpDayExceptions->contains(fn($value) => strtolower($value['day']) === strtolower($day))) {
            $ex = $bpDayExceptions->firstWhere(fn($value) => strtolower($value['day']) === strtolower($day));
            return [
                'start' => $ex['start_time'],
                'end'   => $ex['end_time']
            ];
        }

        if (!empty($hard['bpStartTime']) && !empty($hard['bpEndTime'])) {
            return [
                'start' => $hard['bpStartTime'],
                'end'   => $hard['bpEndTime']
            ];
        }

        return null;
    }

    private function dayHasNoBreak(string $day): bool
    {
        $noBp = collect($this->parsed['hard']['noBp'] ?? []);
        return $noBp->map(fn($d) => strtolower($d))
            ->contains(strtolower($day));
    }

    // ─── Index helper ─────────────────────────────────────────────────────

    private static function indexByDay(array $items, ?string $valueKey = null, bool $groupMultiple = false): array
    {
        return collect($items)
            ->groupBy(fn($item) => strtolower($item['day']))
            ->map(function ($group) use ($valueKey, $groupMultiple) {
                $mapped = $valueKey !== null
                    ? $group->pluck($valueKey)
                    : $group;

                return $groupMultiple ? $mapped->values()->all() : $mapped->first();
            })
            ->all();
    }
}
