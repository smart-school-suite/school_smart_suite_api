<?php

namespace App\Schedular\ExamTimetable\Constraints\Contracts;

use App\Schedular\ExamTimetable\Core\State;

interface ConstraintHandler
{
    /** Handlers that pin sessions run before placement, handlers that review the result run after it. */
    public const BEFORE_PLACEMENT = 'before';
    public const AFTER_PLACEMENT = 'after';

    public static function supports(): string;

    public function phase(): string;

    public function handle(State $state): void;
}
