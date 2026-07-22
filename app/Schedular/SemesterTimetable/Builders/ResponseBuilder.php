<?php

namespace App\Schedular\SemesterTimetable\Builders;

use App\Models\Courses;
use App\Models\Hall;
use App\Models\Teacher;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Core\DiagnosticRegistry;
use App\Schedular\SemesterTimetable\Core\State;
use App\Schedular\SemesterTimetable\DTO\ResponseDTO;
use App\Schedular\SemesterTimetable\DTO\TimetableContext;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionContext;
use App\Schedular\SemesterTimetable\Suggestion\Engine\SuggestionEngine;

class ResponseBuilder extends TimetableContext
{
    public function build(State $state): ResponseDTO
    {
        $diagnosticBuilder = app(DiagnosticRegistry::class);
        $suggestionEngine = app(SuggestionEngine::class);
        $response = new ResponseDTO();
        $response->status = match (true) {
            !empty($state->violations["hard"]) => "error",
            !empty($state->violations["soft"]) => "partial",
            default => "optimal",
        };
        $response->timetable = $this->formatAndGroupTimetableByDay($state->grid);
        $diagnostics = [
            "hard" => $diagnosticBuilder->build($state->violations["hard"] ?? []),
            "soft" => $diagnosticBuilder->build($state->violations["soft"] ?? [])
        ];
        $this->seedSuggestionContext($state, $diagnostics);
        $response->diagnostics = $diagnostics;
        $generatedSuggestions = $suggestionEngine->generate([
            "hard" => $diagnostics["hard"]->toArray() ?? [],
            "soft" => $diagnostics["soft"]->toArray() ?? []
        ] ?? []);
        $response->suggestions = $generatedSuggestions;
        $response->options = $this->formatOptions($generatedSuggestions) ?? [];
        return $response;
    }

    private function formatOptions(array $suggestions)
    {
        return  collect($suggestions)
            ->map(function ($scenarios, $day) {
                return collect($scenarios)
                    ->flatMap(function ($scenario) {
                        return collect($scenario->flow ?? [])
                            ->flatMap(function ($step) {
                                return $step['options'] ?? [];
                            });
                    })
                    ->unique('option_id')
                    ->values()
                    ->all();
            })
            ->filter()
            ->all();
    }
    private function seedSuggestionContext(State $state, array $diagnostics)
    {
        SuggestionContext::setTimetableGrid($state->grid);
        SuggestionContext::setDiagnostics($diagnostics);
        SuggestionContext::setPreferenceMode(self::isWithPreference());
    }
    private function formatAndGroupTimetableByDay(array $grid): array
    {
        $groupedByDay = [];

        foreach ($grid as $slot) {
            $day = strtolower($slot->day ?? '');

            if (!isset($groupedByDay[$day])) {
                $groupedByDay[$day] = [];
            }

            $hall = Hall::with(['types'])->find($slot->hall_id);
            $course = Courses::with(['types'])->find($slot->course_id);
            $teacher = Teacher::find($slot->teacher_id);
            $formattedSlot = [
                'start_time' => $slot->start_time ?? null,
                'end_time' => $slot->end_time ?? null,
                'teacher_id' => $slot->teacher_id ?? null,
                'teacher_name' => $teacher->name ?? null,
                'teacher_picture' => $teacher->profile_picture ?? null,
                'course_id' => $slot->course_id ?? null,
                'course_name' => $course->course_title ?? null,
                'course_credit' => $course->credit ?? null,
                'course_type' => $course->types ?? null,
                'hall_name' => $hall->name ?? null,
                'hall_capacity' => $hall->capacity ?? null,
                'hall_type' => $hall->types ?? null,
                'hall_location' => $hall->location ?? null,
                'hall_id' => $slot->hall_id ?? null,
                // "slot_type" => ($slot->teacher_id === null && $slot->course_id === null && $slot->hall_id === null)
                //     ? GridSlotDTO::TYPE_FREE
                //     : $slot->type,
                "slot_type" => $slot->type,
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
