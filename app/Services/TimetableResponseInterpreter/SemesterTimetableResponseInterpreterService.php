<?php

namespace App\Services\TimetableResponseInterpreter;

use App\Models\Constraint\SemesterTimetableConstraint;

class SemesterTimetableResponseInterpreterService
{
    public function interpret()
    {
        return  collect($this->partialSchedulerResponseMock()["diagnostics"]);
    }

    private static function partialSchedulerResponseMock()
    {
        $filePath = public_path("schedulerResponse/partial.response.example.json");
        $content = file_get_contents($filePath);
        $data = json_decode($content, true);
        return $data;
    }
}
