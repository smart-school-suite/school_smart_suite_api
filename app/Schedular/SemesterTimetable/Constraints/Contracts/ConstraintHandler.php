<?php

namespace App\Schedular\SemesterTimetable\Constraints\Contracts;

use App\Schedular\SemesterTimetable\Core\State;

interface ConstraintHandler
{
    public static function supports(): string;
    public function handle(array $constraints, State $state): void;
}
