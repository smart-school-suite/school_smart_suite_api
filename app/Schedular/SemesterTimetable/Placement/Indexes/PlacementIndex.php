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
    private array $teacherTotalMinutes = [];
    private array $hallTotalMinutes = [];

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
            $start = TimeHelper::toMinutes($tb['start_time']);
            $end   = TimeHelper::toMinutes($tb['end_time']);

            $this->teacherBusy[$tb['teacher_id']][] = [
                'day'    => strtolower($tb['day']),
                'start'  => $start,
                'end'    => $end,
                'placed' => false,
            ];
            $this->addTeacherMinutes($tb['teacher_id'], $end - $start);
        }

        foreach ($requestPayload['hall_busy_periods'] ?? [] as $hb) {
            $start = TimeHelper::toMinutes($hb['start_time']);
            $end   = TimeHelper::toMinutes($hb['end_time']);

            $this->hallBusy[$hb['hall_id']][] = [
                'day'    => strtolower($hb['day']),
                'start'  => $start,
                'end'    => $end,
                'placed' => false,
            ];
            $this->addHallMinutes($hb['hall_id'], $end - $start);
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
        $this->addTeacherMinutes($teacherId, $end - $start);
    }

    public function commitHallPeriod(string $hallId, string $day, int $start, int $end): void
    {
        $this->hallBusy[$hallId][] = [
            'day'    => $day,
            'start'  => $start,
            'end'    => $end,
            'placed' => true,
        ];
        $this->addHallMinutes($hallId, $end - $start);
    }

    private function addTeacherMinutes(string $teacherId, int $minutes): void
    {
        $this->teacherTotalMinutes[$teacherId] = ($this->teacherTotalMinutes[$teacherId] ?? 0) + $minutes;
    }

    private function addHallMinutes(string $hallId, int $minutes): void
    {
        $this->hallTotalMinutes[$hallId] = ($this->hallTotalMinutes[$hallId] ?? 0) + $minutes;
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
     * Running total of busy minutes for a teacher across the whole week.
     * Counts both payload-seeded and placement-grown periods.
     */
    public function teacherTotalMinutes(string $teacherId): int
    {
        return $this->teacherTotalMinutes[$teacherId] ?? 0;
    }

    /**
     * Running total of busy minutes for a hall across the whole week.
     * Counts both payload-seeded and placement-grown periods.
     */
    public function hallTotalMinutes(string $hallId): int
    {
        return $this->hallTotalMinutes[$hallId] ?? 0;
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
