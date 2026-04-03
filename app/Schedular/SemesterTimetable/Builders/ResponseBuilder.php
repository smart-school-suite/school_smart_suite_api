<?php

namespace App\Schedular\SemesterTimetable\Builders;

use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Core\DiagnosticRegistry;
use App\Schedular\SemesterTimetable\Core\State;
use App\Schedular\SemesterTimetable\DTO\ResponseDTO;

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
        $response->timetable = $state->grid;
        $response->diagnostics = [
            "constraints" => [
                "hard" => $diagnosticBuilder->build($state->violations["hard"] ?? []),
                "soft" => $diagnosticBuilder->build($state->violations["soft"] ?? [])
            ]
        ];
        return $response;
    }
}
