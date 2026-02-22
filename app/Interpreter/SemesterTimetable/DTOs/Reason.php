<?php

namespace App\Interpreter\SemesterTimetable\DTOs;

class Reason
{
    public function __construct(
        public string $title,
        public string $description,
        public array $context = []
    ) {}
}
