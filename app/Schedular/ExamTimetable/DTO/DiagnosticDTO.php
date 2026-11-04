<?php

namespace App\Schedular\ExamTimetable\DTO;

class DiagnosticDTO
{
    public string $id;
    public array $constraint_failed = [];
    public array $blockers = [];
}
