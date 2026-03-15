<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\Core;

use App\Interpreter\SemesterTimetable\Suggestion\Registry\ConstraintSuggestionRegistry;

class ConstraintSuggestionEngine
{

    public function __construct(private ConstraintSuggestionRegistry $registry) {}

    public function generate(array $diagnostic): ?array
    {
        $constraint = $diagnostic['constraint'];

        $builderClass = $this->registry->get($constraint);

        if (!$builderClass) {
            return null;
        }

        $builder = app($builderClass);

        return $builder->suggest($diagnostic);
    }
}
