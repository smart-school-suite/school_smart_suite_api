<?php

namespace App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Contracts;

use App\Schedular\ExamTimetable\DTO\DiagnosticDTO;

interface DiagnosticBuilder
{
    public function supports(string $type): bool;
    public function build(array $diagnostic): DiagnosticDTO;
}
