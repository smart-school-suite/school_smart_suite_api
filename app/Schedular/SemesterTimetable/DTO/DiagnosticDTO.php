<?php

namespace App\Schedular\SemesterTimetable\DTO;

class DiagnosticDTO
{
    public string $id;
    public array $constraint_failed = [];
    public array $blockers = [];
    public array $suggestions = [];
    public function __construct()
    {
        //
    }
}
