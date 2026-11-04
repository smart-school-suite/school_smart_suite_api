<?php

namespace App\Schedular\ExamTimetable\Builders\ResponseBuilder;

use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Core\DiagnosticRegistry;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\DTO\ExamDateDTO;
use App\Schedular\ExamTimetable\DTO\ExamSessionDTO;
use App\Schedular\ExamTimetable\DTO\ResponseDTO;
use App\Schedular\ExamTimetable\Solution\SolutionEngine;
use App\Schedular\ExamTimetable\Support\Time;
use Carbon\Carbon;
use Throwable;

class ResponseBuilder
{
    public function __construct(private DiagnosticRegistry $diagnostics) {}

    public function build(State $state, bool $withSolutions = true): ResponseDTO
    {
        $response = new ResponseDTO();
        $response->status = match (true) {
            !empty($state->violations['hard']) => 'error',
            !empty($state->violations['soft']) => 'partial',
            default => 'optimal',
        };
        $response->timetable = $this->timetable($state);
        $response->unscheduled = $this->unscheduled($state);
        $response->diagnostics = [
            'hard' => $this->diagnostics->build($state->violations['hard']),
            'soft' => $this->diagnostics->build($state->violations['soft']),
        ];

        if ($withSolutions) {
            try {
                $response->suggestions = app(SolutionEngine::class)->generate(
                    [
                        'hard' => $response->diagnostics['hard']->all(),
                        'soft' => $response->diagnostics['soft']->all(),
                    ],
                    $state->context->payload(),
                    $state,
                );
            } catch (Throwable $exception) {
                report($exception);
                $response->suggestions = [];
            }
        }

        $response->options = SolutionEngine::options($response->suggestions);

        return $response;
    }

    private function timetable(State $state): array
    {
        $context = $state->context;

        return collect($state->dateGrid)
            ->filter(fn(ExamDateDTO $day) => !empty($day->sessions))
            ->map(fn(ExamDateDTO $day) => [
                'date' => $day->date,
                'day' => strtolower(Carbon::parse($day->date)->format('l')),
                'opens_at' => Time::toTime($day->start),
                'closes_at' => Time::toTime($day->end),
                'sessions' => collect($day->sessions)->map(fn(ExamSessionDTO $session) => [
                    'type' => $session->type,
                    'source' => $session->source,
                    'course_id' => $session->courseId,
                    'course_name' => $context->courseName($session->courseId),
                    'date' => $session->date,
                    'start_time' => $session->startTime(),
                    'end_time' => $session->endTime(),
                    'duration' => $session->end - $session->start,
                    'candidate_count' => $session->candidateCount,
                    'halls' => collect($session->halls)->map(fn(array $hall) => [
                        'hall_id' => $hall['hall_id'],
                        'hall_name' => $hall['hall_name'] ?? $context->hallName($hall['hall_id']),
                        'capacity' => $hall['capacity'] ?? null,
                        'candidate_count' => $hall['candidate_count'],
                        'shared' => (bool) ($hall['shared'] ?? false),
                        'invigilators' => collect($hall['invigilators'] ?? [])->map(fn($id) => [
                            'invigilator_id' => $id,
                            'name' => $context->invigilatorName($id),
                        ])->values()->all(),
                    ])->values()->all(),
                ])->values()->all(),
            ])
            ->values()
            ->all();
    }

    private function unscheduled(State $state): array
    {
        $scheduled = $state->scheduledCourseIds();

        return $state->context->courses()
            ->reject(fn(array $course) => in_array($course['course_id'], $scheduled, true))
            ->map(fn(array $course) => [
                'course_id' => $course['course_id'],
                'course_name' => $course['course_name'] ?? null,
            ])
            ->values()
            ->all();
    }
}
