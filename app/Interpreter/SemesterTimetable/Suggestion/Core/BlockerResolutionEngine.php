<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\Core;

use App\Interpreter\SemesterTimetable\Suggestion\Registry\BlockerSuggestionRegistry;

class BlockerResolutionEngine
{
    public function __construct(
        private BlockerSuggestionRegistry $registry
    ) {}

    public function generate(array $blockerResolutions): array
    {
        $results = [];

        foreach ($blockerResolutions as $blockerResolution) {

            $builderClass = $this->registry->get($blockerResolution['violation']);

            if (!$builderClass) {
                continue;
            }

            $builder = app($builderClass);

            $results[] = $builder->suggest($blockerResolution);
        }

        return $results;
    }
}
