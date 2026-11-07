<?php

namespace App\Schedular\ExamTimetable\Core;

use App\Schedular\ExamTimetable\DTO\ExamDateDTO;
use App\Schedular\ExamTimetable\Placement\Indexes\ExamPlacementIndex;
use App\Schedular\ExamTimetable\Placement\Support\Time;

class State
{
    /** @var array<string, ExamDateDTO> keyed by date */
    public array $dateGrid = [];
    public array $violations = ["hard" => [], "soft" => []];
    public ?ExamPlacementIndex $index = null;

    /** @var array<string, array> course_id => the session that examines it */
    public array $placedCourses = [];

    public function __construct() {}

    public function isPlaced(string $courseId): bool
    {
        return isset($this->placedCourses[$courseId]);
    }

    /** @return array[] the exam sessions of a date */
    public function sessions(string $date): array
    {
        return $this->dateGrid[$date]->allocations ?? [];
    }

    public function load(string $date): int
    {
        return count($this->sessions($date));
    }

    /** @return array[] sessions of a date overlapping a window */
    public function sessionsOverlapping(string $date, int $start, int $end): array
    {
        return array_values(array_filter(
            $this->sessions($date),
            fn(array $session) => Time::overlaps($start, $end, Time::toMinutes($session['start_time']), Time::toMinutes($session['end_time']))
        ));
    }

    public function addSession(string $date, array $session): void
    {
        $this->dateGrid[$date]->allocations[] = $session;
        $this->placedCourses[$session['course_id']] = [...$session, 'date' => $date];
    }

    /** Adds an invigilator to a hall of a session already placed. */
    public function addInvigilator(string $date, string $courseId, string $hallId, string $invigilatorId): void
    {
        foreach ($this->dateGrid[$date]->allocations as &$session) {
            if ($session['course_id'] !== $courseId) {
                continue;
            }
            foreach ($session['halls'] as &$hall) {
                if ($hall['hall_id'] === $hallId) {
                    $hall['invigilators'][] = $invigilatorId;
                }
            }
        }
    }
}
