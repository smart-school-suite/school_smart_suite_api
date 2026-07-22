<?php

namespace App\Schedular\ExamTimetable\Context;

use App\Schedular\ExamTimetable\Context\ExamTimetableContext;
use Illuminate\Support\Collection;

class RequestContext  extends ExamTimetableContext
{
    private function __construct(private readonly array $parsed) {}

    public static function fromPayload(): self
    {
        return new self([
            'hard' => self::parseHard(self::$requestPayload['hard_constraints']  ?? [], self::$requestPayload),
            'soft' => self::parseSoft(self::$requestPayload['soft_constraints'] ?? []),
        ]);
    }
    private static function parseHard(array $hc, array $requestPayload): array
    {
        return [
            'startDate'        => $requestPayload['start_date']                  ?? [],
            'endDate'           => $requestPayload['end_date']                     ?? [],
            'invigs'      => $requestPayload['invigilators']      ?? [],
            'invigBusySlots'      => $requestPayload['invigilator_busy_slots']         ?? [],
            'courses'        => $requestPayload['courses']           ?? [],
            'halls' => $requestPayload['halls'] ?? [],
            'hBusySlots' => $requestPayload['hall_busy_slots'] ?? [],
            'cCount' => $requestPayload['candidate_count'] ?? 0,
            'specialty' => $requestPayload['specialty_id'] ?? null,

            'opStartTime'     => $hc['operational_hours']['start_time']      ?? '08:00',
            'opEndTime'       => $hc['operational_hours']['end_time']         ?? '17:00',
            'opDayExceptions' => $hc['operational_hours']['date_exceptions'] ?? [],
            'opDateExclusion'  => $hc['operational_hours']['date_exclusions'] ?? [],

            'sessionDuration'  => (int) ($hc['session_duration']['duration_minutes'] ?? 60),
            'sdExceptions'    => $hc['session_duration']['course_exceptions'] ?? [],

            'jointCourses'    => $hc['required_joint_course_periods'] ?? [],
        ];
    }
    private static function parseSoft(array $sc): array
    {
        return [
            'requestedAssignments'   => $sc['requested_assignments']          ?? [],
            'iRequestedSlot'            => $sc['invigilator_requested_slot']            ?? null,
            'cRequestedSlot'      => $sc['requested_course_slot'] ?? [],
            'sDailyLoadRange'           => $sc['student_daily_load_range']       ?? null
        ];
    }

    //soft constraints

    public function specialty(): ?string
    {
        return $this->parsed['hard']['specialty'] ?? null;
    }
    public function sDailyLoadRange(): ?array
    {
        $sDailyLoadRange = $this->parsed['soft']['sDailyLoadRange'];

        return [
            'min_sessions' => $sDailyLoadRange['min_sessions'] ?? null,
            'max_sessions' => $sDailyLoadRange['max_sessions'] ?? null,
        ];
    }
    public function sDailyLoadRangeDate(string $date): ?array
    {
        $sDailyLoadRange = $this->sDailyLoadRange();

        if (!$sDailyLoadRange) {
            return null;
        }

        $dateException = collect($sDailyLoadRange['date_exceptions'] ?? [])->firstWhere('date', $date);

        return $dateException ? [
            'min_sessions' => $dateException['min_sessions'] ?? null,
            'max_sessions' => $dateException['max_sessions'] ?? null,
        ] : $sDailyLoadRange;
    }
    public function courseRequestedSlots(): Collection
    {
        return collect($this->parsed['soft']['cRequestedSlot']);
    }
    public function courseRequestedSlotDate(string $date): Collection
    {
        return collect($this->courseRequestedSlots())->where('date', $date) ?? collect([]);
    }
    public function requestedAssignments(): Collection
    {
        return collect($this->parsed['soft']['requestedAssignments']);
    }
    public function requestedAssignmentDate(string $date): Collection
    {
        return collect($this->requestedAssignments())->where('date', $date) ?? collect([]);
    }
    public function invigilatorRequestedSlot(): Collection
    {
        return collect($this->parsed['soft']['iRequestedSlot']);
    }
    public function invigRequestedSlotDate(string $date): ?array
    {
        $slot = $this->invigilatorRequestedSlot();
        return ($slot && $slot['date'] === $date) ? $slot : null;
    }
    public function invigRequestedSlot(): Collection
    {
        return collect($this->invigRequestedSlot() ? [$this->invigRequestedSlot()] : []);
    }
    //hard constraints

    public function candidateCount(): int
    {
        return $this->parsed['hard']['cCount'] ?? 0;
    }
    public function invigilators(): Collection
    {
        return collect($this->parsed['hard']['invigs']);
    }
    public function invigilator(string $invigilatorId): ?array
    {
        return $this->invigilators()->firstWhere('invigilator_id', $invigilatorId);
    }
    public function invigCoursesTaught(string $invigilatorId): Collection
    {
        return collect($this->invigilators()->firstWhere('invigilator_id', $invigilatorId)['courses_taught'] ?? []);
    }
    public function halls(): Collection
    {
        return collect($this->parsed['hard']['halls']);
    }

    public function hallCapacity(string $hallId): int
    {
        return $this->halls()->firstWhere('hall_id', $hallId)['capacity'] ?? 0;
    }
    public function hall(string $hallId): ?array
    {
        return $this->halls()->firstWhere('hall_id', $hallId);
    }
    public function hallBusySlots(string $hallId): Collection
    {
        return collect($this->parsed['hard']['hBusySlots'])->where('hall_id', $hallId) ?? collect([]);
    }
    public function courses(): Collection
    {
        return collect($this->parsed['hard']['courses']);
    }
    public function examDuration(): array
    {
        return [
            'start_date' => $this->parsed['hard']['startDate'],
            'end_date' => $this->parsed['hard']['endDate'],
        ];
    }
    public function examStartDate(): string
    {
        return $this->parsed['hard']['startDate'];
    }
    public function examEndDate(): string
    {
        return $this->parsed['hard']['endDate'];
    }
    public function sessionDuration(): int
    {
        return $this->parsed['hard']['sessionDuration'];
    }

    public function sessionDurationCourse(string $courseId): ?int
    {
        return collect($this->parsed['hard']['sdExceptions'])->firstWhere('course_id', $courseId)['duration']
            ?? $this->sessionDuration();
    }
    public function operationalHours(): array
    {
        return [
            'start_time' => $this->parsed['hard']['opStartTime'],
            'end_time' => $this->parsed['hard']['opEndTime'],
        ];
    }
    public function operationalHourDate(string $date): ?array
    {
        if (collect($this->parsed['hard']['opDateExclusion'])->contains($date)) {
            return null;
        }
        return collect($this->parsed['hard']['opDayExceptions'])->firstWhere('date', $date)
            ?? $this->operationalHours();
    }

    public function isOperationalDate(string $date): bool
    {
        return collect($this->operationalDates())->contains($date);
    }

    public function operationalDates(): array
    {
        $exludedDates = collect($this->parsed['hard']['opDateExclusion']);
        $startDate = $this->parsed['hard']['startDate'];
        $endDate = $this->parsed['hard']['endDate'];

        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);

        $dates = [];

        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            if (!$exludedDates->contains($formattedDate)) {
                $dates[] = $formattedDate;
            }
        }

        return $dates;
    }

    public function invigilatorBusySlots(string $invigilatorId): Collection
    {
        return collect($this->parsed['hard']['invigBusySlots'])->where('invigilator_id', $invigilatorId) ?? collect([]);
    }

    public function invigilatorBusySlotDate(string $invigilatorId, string $date): Collection
    {
        return collect($this->invigilatorBusySlots($invigilatorId))->where('date', $date) ?? collect([]);
    }

    public function jointCourseDate(string $date): Collection
    {
        return collect($this->parsed['hard']['jointCourses'])->where('date', $date) ?? collect([]);
    }
    public function jointCourses(): Collection
    {
        return collect($this->parsed['hard']['jointCourses']);
    }
}
