<?php

namespace App\Schedular\SemesterTimetable\Builders;

use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Core\DiagnosticRegistry;
use App\Schedular\SemesterTimetable\Core\State;
use App\Schedular\SemesterTimetable\DTO\GridSlotDTO;
use App\Schedular\SemesterTimetable\DTO\ResponseDTO;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionContext;

class ResponseBuilder
{
    public function build(State $state): ResponseDTO
    {
        $diagnosticBuilder = app(DiagnosticRegistry::class);
        $response = new ResponseDTO();
        $response->status = match (true) {
            !empty($state->violations["hard"]) => "error",
            !empty($state->violations["soft"]) => "partial",
            default => "optimal",
        };
        $response->timetable = $this->formatAndGroupTimetableByDay($state->grid);
        $diagnostics = [
            "constraints" => [
                "hard" => $diagnosticBuilder->build($state->violations["hard"] ?? []),
                "soft" => $diagnosticBuilder->build($state->violations["soft"] ?? [])
            ]
        ];
        $this->seedSuggestionContext($state, $diagnostics);
        $response->diagnostics = $diagnostics;
        return $response;
    }

    private function seedSuggestionContext($state, $diagnostics){
        SuggestionContext::setTimetableGrid($state->grid);
        SuggestionContext::setDiagnostics($diagnostics);
    }
    private function formatAndGroupTimetableByDay(array $grid): array
    {
        $groupedByDay = [];

        foreach ($grid as $slot) {
            $day = strtolower($slot->day ?? '');

            if (!isset($groupedByDay[$day])) {
                $groupedByDay[$day] = [];
            }

            $formattedSlot = [
                'start_time' => $slot->start_time ?? null,
                'end_time' => $slot->end_time ?? null,
                'teacher_id' => $slot->teacher_id ?? null,
                'course_id' => $slot->course_id ?? null,
                'hall_id' => $slot->hall_id ?? null,
                "slot_type" => ($slot->teacher_id === null && $slot->course_id === null && $slot->hall_id === null)
                    ? GridSlotDTO::TYPE_FREE
                    : $slot->type,
            ];

            $groupedByDay[$day][] = $formattedSlot;
        }

        $result = [];
        $dayOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        foreach ($dayOrder as $day) {
            if (!isset($groupedByDay[$day])) {
                continue;
            }

            usort($groupedByDay[$day], function ($a, $b) {
                $timeA = strtotime($a['start_time']);
                $timeB = strtotime($b['start_time']);
                return $timeA <=> $timeB;
            });

            $result[] = [
                'day' => $day,
                'slots' => $groupedByDay[$day]
            ];
        }

        return $result;
    }
}
