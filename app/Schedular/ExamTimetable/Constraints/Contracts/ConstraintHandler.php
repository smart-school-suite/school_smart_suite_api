<?php

namespace App\Schedular\ExamTimetable\Constraints\Contracts;

use App\Schedular\ExamTimetable\Core\State;

interface ConstraintHandler
{
    /** Runs before the placement engine: the request fixes a course in place. */
    public const BEFORE_PLACEMENT = 'before_placement';

    /** Runs after the placement engine: the request is checked against the result. */
    public const AFTER_PLACEMENT = 'after_placement';

    public static function supports(): string;

    public static function stage(): string;

    public function handle(State $state): void;
}
