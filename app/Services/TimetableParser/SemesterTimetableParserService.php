<?php

namespace App\Services\TimetableParser;

use App\Interpreter\SemesterTimetable\Core\DiagnosticResponseBuilder;
use App\Models\Constraint\SemesterTimetableConstraint;

class SemesterTimetableParserService
{
    protected DiagnosticResponseBuilder $diagnosticResponseBuilder;
    public function __construct(DiagnosticResponseBuilder $diagnosticResponseBuilder)
    {
        $this->diagnosticResponseBuilder = $diagnosticResponseBuilder;
    }

    public function interpret()
    {
        $diagnostics = $this->partialSchedulerResponseMock()["diagnostics"]['constraints']['soft'];
        // return $diagnostics;
        return $this->diagnosticResponseBuilder->build($diagnostics);
    }

    private static function partialSchedulerResponseMock()
    {
        $filePath = public_path("schedulerResponse/partial.response.example.json");
        $content = file_get_contents($filePath);
        $data = json_decode($content, true);
        return $data;
    }
}
