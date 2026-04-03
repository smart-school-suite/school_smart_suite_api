<?php

namespace App\Schedular\SemesterTimetable\DTO;

class ResponseDTO
{
    public string $status;
    public array $timetable;
    public array $diagnostics;
    public function __construct() {}
}
