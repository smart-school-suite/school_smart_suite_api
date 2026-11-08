<?php

namespace App\Schedular\ExamTimetable\Builders\ResponseBuilder;

use App\Constant\Constraint\ExamTimetable\Course\CoursePlacement;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Core\DiagnosticRegistry;
use App\Schedular\ExamTimetable\Context\ExamTimetableContext;
use App\Schedular\ExamTimetable\Context\RequestContext;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\DTO\DiagnosticDTO;
use App\Schedular\ExamTimetable\DTO\ResponseDTO;
use App\Schedular\ExamTimetable\Placement\Support\Time;
use App\Schedular\ExamTimetable\Solution\SolutionEngine;
use Carbon\Carbon;
use Throwable;

class ResponseBuilder
{
    public function build(State $state, array $requestPayload = [], bool $withSolutions = false): ResponseDTO
    {
        $diagnostics = app(DiagnosticRegistry::class);

        $response = new ResponseDTO();
        $response->status = match (true) {
            !empty($state->violations["hard"]) => "error",
            !empty($state->violations["soft"]) => "partial",
            default => "optimal",
        };
        $response->timetable = $this->formatTimetable($state);
        $response->diagnostics = [
            "hard" => $diagnostics->build($state->violations["hard"] ?? []),
            "soft" => $diagnostics->build($state->violations["soft"] ?? []),
        ];
        $response->unplaced_courses = collect($state->violations['hard'] ?? [])
            ->filter(fn(array $violation) => $violation['constraint_failed']['key'] === CoursePlacement::KEY)
            ->pluck('constraint_failed.course_id')
            ->values()
            ->all();
        $response->suggestions = $withSolutions && $response->status !== 'optimal'
            ? $this->suggestions($response, $requestPayload, $state)
            : [];

        return $response;
    }

    private function suggestions(ResponseDTO $response, array $requestPayload, State $state): array
    {
        try {
            return app(SolutionEngine::class)->generate([
                'hard' => $response->diagnostics['hard']->map(fn(DiagnosticDTO $diagnostic) => $diagnostic->toArray())->all(),
                'soft' => $response->diagnostics['soft']->map(fn(DiagnosticDTO $diagnostic) => $diagnostic->toArray())->all(),
            ], $requestPayload, $state);
        } catch (Throwable $exception) {
            report($exception);
            return [];
        } finally {
            // Verifying solutions re-runs the scheduler; put this run's input back.
            ExamTimetableContext::setRequestPayload($requestPayload);
        }
    }

    /**
     * Every exam date in order with its sessions by start time. Names come
     * from the scheduler input so the timetable reads without more lookups.
     */
    private function formatTimetable(State $state): array
    {
        $context = RequestContext::fromPayload();
        $days = [];

        foreach ($state->dateGrid as $date => $day) {
            $sessions = $day->allocations;
            usort($sessions, fn(array $a, array $b) => strcmp($a['start_time'], $b['start_time']));

            $days[] = [
                'date' => $date,
                'day' => strtolower(Carbon::parse($date)->format('l')),
                'start_time' => $day->startTime,
                'end_time' => $day->endTime,
                'slots' => array_map(fn(array $session) => $this->formatSession($context, $session), $sessions),
            ];
        }

        return $days;
    }

    private function formatSession(RequestContext $context, array $session): array
    {
        $course = $context->course($session['course_id']) ?? [];

        return [
            'course_id' => $session['course_id'],
            'course_title' => $course['course_title'] ?? null,
            'course_code' => $course['course_code'] ?? null,
            'start_time' => $session['start_time'],
            'end_time' => $session['end_time'],
            'duration_minutes' => Time::duration($session['start_time'], $session['end_time']),
            'slot_type' => $session['type'],
            'source' => $session['source'],
            'candidate_count' => $session['candidate_count'],
            'halls' => array_map(fn(array $hall) => [
                'hall_id' => $hall['hall_id'],
                'hall_name' => $hall['hall_name'] ?? null,
                'capacity' => $hall['capacity'],
                'candidate_count' => $hall['candidate_count'],
                'shared' => $hall['shared'],
                'invigilators' => array_map(fn(string $id) => [
                    'invigilator_id' => $id,
                    'name' => $context->invigilator($id)['name'] ?? null,
                ], $hall['invigilators']),
            ], $session['halls']),
        ];
    }
}
