<?php

namespace App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Contracts;

use App\Schedular\SemesterTimetable\DTO\BlockerDTO;

interface BlockerBuilder
{
    public static function type(): string;
    public function build($blocker): BlockerDTO;
}
