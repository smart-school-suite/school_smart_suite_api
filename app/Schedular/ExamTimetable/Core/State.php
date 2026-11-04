<?php

namespace App\Schedular\ExamTimetable\Core;

use App\Schedular\ExamTimetable\Context\RequestContext;
use App\Schedular\ExamTimetable\DTO\ExamDateDTO;
use App\Schedular\ExamTimetable\DTO\ExamSessionDTO;

class State
{
    /** @var array<string, ExamDateDTO> keyed by Y-m-d */
    public array $dateGrid = [];
    public array $violations = ["hard" => [], "soft" => []];

    public function __construct(public readonly RequestContext $context) {}

    public function sessionsOn(string $date): array
    {
        return isset($this->dateGrid[$date]) ? $this->dateGrid[$date]->sessions : [];
    }

    /** @return ExamSessionDTO[] */
    public function sessions(): array
    {
        return collect($this->dateGrid)->flatMap(fn(ExamDateDTO $day) => $day->sessions)->values()->all();
    }

    public function scheduledCourseIds(): array
    {
        return collect($this->sessions())->pluck('courseId')->unique()->values()->all();
    }

    public function isScheduled(string $courseId): bool
    {
        return in_array($courseId, $this->scheduledCourseIds(), true);
    }

    public function place(ExamSessionDTO $session): void
    {
        $this->dateGrid[$session->date]->sessions[] = $session;
        usort($this->dateGrid[$session->date]->sessions, fn(ExamSessionDTO $a, ExamSessionDTO $b) => $a->start <=> $b->start);
    }

    public function load(string $date): int
    {
        return count($this->sessionsOn($date));
    }

    public function addViolation(string $severity, array $violation): void
    {
        $this->violations[$severity][] = $violation;
    }
}
