<?php

namespace App\Constant\Timetable\Constraints\Guides;

class ConstraintGuide
{
    public function __construct(
        public readonly string $key,
        public readonly string $intent,
        public readonly string $whenToUse,
        public readonly array $requiredFields,
        public readonly array $optionalFields,
        public readonly array $interpretationRules,
        public readonly array $examples,
    ) {}
}
