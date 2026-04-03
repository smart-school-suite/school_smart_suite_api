<?php

namespace App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Contracts;

use App\Schedular\SemesterTimetable\DTO\DiagnosticDTO;

interface DiagnosticBuilder
{
    public static function type(): string;
    public function build($blocker): DiagnosticDTO;
}
