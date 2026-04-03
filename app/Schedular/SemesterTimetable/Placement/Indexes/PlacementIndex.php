<?php

namespace App\Schedular\SemesterTimetable\Placement\Indexes;
use App\Schedular\SemesterTimetable\Placement\Support\TimeHelper;

class PlacementIndex
{
    private array $courseTeachers = [];
    private array $teacherPreferences = [];
    private array $teacherBusy = [];
    private array $hallBusy = [];
    private array $halls = [];

    public static function fromPayload(array $requestPayload): self
    {
        $index = new self();
        $index->hydrate($requestPayload);
        return $index;
    }

    private function hydrate(array $requestPayload): void
    {
        foreach ($requestPayload['teacher_courses'] ?? [] as $tc) {
            $this->courseTeachers[$tc['course_id']][] = $tc['teacher_id'];
        }

        foreach ($requestPayload['teacher_preferred_periods'] ?? [] as $tp) {
            $this->teacherPreferences[$tp['teacher_id']][] = [
                'day'   => strtolower($tp['day']),
                'start' => TimeHelper::toMinutes($tp['start_time']),
                'end'   => TimeHelper::toMinutes($tp['end_time']),
            ];
        }

        foreach ($requestPayload['teacher_busy_periods'] ?? [] as $tb) {
            $this->teacherBusy[$tb['teacher_id']][] = [
                'day'    => strtolower($tb['day']),
                'start'  => TimeHelper::toMinutes($tb['start_time']),
                'end'    => TimeHelper::toMinutes($tb['end_time']),
                'placed' => false,
            ];
        }

        foreach ($requestPayload['hall_busy_periods'] ?? [] as $hb) {
            $this->hallBusy[$hb['hall_id']][] = [
                'day'    => strtolower($hb['day']),
                'start'  => TimeHelper::toMinutes($hb['start_time']),
                'end'    => TimeHelper::toMinutes($hb['end_time']),
                'placed' => false,
            ];
        }

        foreach ($requestPayload['halls'] ?? [] as $hall) {
            $this->halls[$hall['hall_id']] = $hall;
        }
    }

    // ─── Grow (called by PlacementEngine after each commit) ───────────────

    public function commitTeacherPeriod(string $teacherId, string $day, int $start, int $end): void
    {
        $this->teacherBusy[$teacherId][] = [
            'day'    => $day,
            'start'  => $start,
            'end'    => $end,
            'placed' => true,
        ];
    }

    public function commitHallPeriod(string $hallId, string $day, int $start, int $end): void
    {
        $this->hallBusy[$hallId][] = [
            'day'    => $day,
            'start'  => $start,
            'end'    => $end,
            'placed' => true,
        ];
    }

    // ─── Queries ──────────────────────────────────────────────────────────

    public function courseTeachers(): array
    {
        return $this->courseTeachers;
    }

    public function halls(): array
    {
        return $this->halls;
    }

    public function teacherPreferences(string $teacherId): array
    {
        return $this->teacherPreferences[$teacherId] ?? [];
    }

    public function teacherBusy(string $teacherId): array
    {
        return $this->teacherBusy[$teacherId] ?? [];
    }

    public function hallBusy(string $hallId): array
    {
        return $this->hallBusy[$hallId] ?? [];
    }

    /**
     * Total busy minutes for a teacher on a specific day.
     * Counts both payload-seeded and placement-grown periods.
     */
    public function teacherBusyMinutesOnDay(string $teacherId, string $day): int
    {
        return $this->sumMinutesOnDay($this->teacherBusy[$teacherId] ?? [], $day);
    }

    /**
     * Total busy minutes for a hall on a specific day.
     * Counts both payload-seeded and placement-grown periods.
     */
    public function hallBusyMinutesOnDay(string $hallId, string $day): int
    {
        return $this->sumMinutesOnDay($this->hallBusy[$hallId] ?? [], $day);
    }

    private function sumMinutesOnDay(array $periods, string $day): int
    {
        $total = 0;
        foreach ($periods as $period) {
            if ($period['day'] === $day) {
                $total += $period['end'] - $period['start'];
            }
        }
        return $total;
    }
}
