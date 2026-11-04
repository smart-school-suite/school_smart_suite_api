<?php

namespace App\Schedular\ExamTimetable\DTO;

class ResponseDTO
{
    public string $status;
    public array $timetable = [];
    public array $diagnostics = [];
    public array $suggestions = [];
    public array $options = [];
    public array $unscheduled = [];
}
