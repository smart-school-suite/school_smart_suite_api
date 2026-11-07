<?php

namespace App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Contracts;

use App\Schedular\ExamTimetable\DTO\DiagnosticDTO;

interface DiagnosticBuilder
{
    public static function type(): string;
    public function build(array $diagnostic): DiagnosticDTO;
}
