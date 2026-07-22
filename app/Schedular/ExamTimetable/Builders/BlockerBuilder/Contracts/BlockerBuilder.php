<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Contracts;

use App\Schedular\ExamTimetable\DTO\BlockerDTO;

interface BlockerBuilder
{
   public function supports(string $type): bool;
   public function build(array $blocker): BlockerDTO;
}
