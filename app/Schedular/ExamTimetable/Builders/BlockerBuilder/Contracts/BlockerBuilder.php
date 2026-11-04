<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Contracts;

use App\Schedular\ExamTimetable\DTO\BlockerDTO;

interface BlockerBuilder
{
    public static function type(): string;

    public function build(array $blocker): BlockerDTO;
}
