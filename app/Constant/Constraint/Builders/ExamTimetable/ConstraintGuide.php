<?php

namespace App\Constant\Constraint\Builders\ExamTimetable;

class ConstraintGuide
{
    public function __construct(
        public readonly string $name,
        public readonly string $program_name,
        public readonly string $type,
        public readonly string $code,
        public readonly string $description,
        public readonly array $examples,
    ) {}
}
