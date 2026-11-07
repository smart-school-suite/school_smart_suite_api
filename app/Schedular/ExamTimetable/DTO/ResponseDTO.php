<?php

namespace App\Schedular\ExamTimetable\DTO;

class ResponseDTO
{
    /** optimal | partial | error */
    public string $status;
    /** Exam dates in order, each with its sessions. */
    public array $timetable = [];
    /** ['hard' => Collection<DiagnosticDTO>, 'soft' => Collection<DiagnosticDTO>] */
    public array $diagnostics = [];
    /** Course ids that got no exam session. */
    public array $unplaced_courses = [];
    public array $suggestions = [];
    public function __construct() {}
}
