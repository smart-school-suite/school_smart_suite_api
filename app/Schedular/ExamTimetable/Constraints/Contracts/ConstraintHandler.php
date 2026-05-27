<?php

namespace App\Schedular\ExamTimetable\Constraints\Contracts;

use App\Schedular\ExamTimetable\Core\State;

interface ConstraintHandler
{
    public static function supports(): string;
    public function handle(State $state): void;
}
