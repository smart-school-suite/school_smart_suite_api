<?php

namespace App\Schedular\ExamTimetable\Core;

use App\Schedular\ExamTimetable\Context\RequestContext;
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

    /**
     * @param ?string $forCourseId only the sessions sharing candidates with this course
     *                             (every session when all courses have the same candidates)
     * @return array[] the exam sessions of a date
     */
    public function sessions(string $date, ?string $forCourseId = null): array
    {
        $sessions = $this->dateGrid[$date]->allocations ?? [];

        if ($forCourseId === null) {
            return $sessions;
        }

        $context = RequestContext::fromPayload();
        if (!$context->hasCandidateGroups()) {
            return $sessions;
        }

        return array_values(array_filter(
            $sessions,
            fn(array $session) => $session['course_id'] !== $forCourseId && $context->sharesCandidates($forCourseId, $session['course_id'])
        ));
    }

    /**
     * Exams a candidate sits on the date: the number of sessions when all
     * courses have the same candidates, else the busiest candidate's count
     * (only the course's own candidates when $forCourseId is given).
     */
    public function load(string $date, ?string $forCourseId = null): int
    {
        $context = RequestContext::fromPayload();
        $sessions = $this->dateGrid[$date]->allocations ?? [];

        if (!$context->hasCandidateGroups()) {
            return count($sessions);
        }

        $only = $forCourseId !== null ? $context->candidatesOf($forCourseId) : null;
        $counts = [];

        foreach ($sessions as $session) {
            if ($session['course_id'] === $forCourseId) {
                continue;
            }
            $students = $context->candidatesOf($session['course_id']);
            foreach ($only !== null ? array_intersect_key($students, $only) : $students as $student => $_) {
                $counts[$student] = ($counts[$student] ?? 0) + 1;
            }
        }

        return $counts ? max($counts) : 0;
    }

    /** @return array[] sessions of a date overlapping a window (sharing candidates with $forCourseId when given) */
    public function sessionsOverlapping(string $date, int $start, int $end, ?string $forCourseId = null): array
    {
        return array_values(array_filter(
            $this->sessions($date, $forCourseId),
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
