<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Normalization;

use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionContext;
use App\Schedular\SemesterTimetable\Suggestion\Normalization\Registry\ResolverRegistry;
use App\Schedular\SemesterTimetable\Suggestion\Resolution\Core\ResolutionEngine;

class ScenarioNormalizationEngine extends SuggestionContext
{

    public function normalize(array &$scenarios): void
    {
        $resolverRegistry = app(ResolverRegistry::class);

        foreach ($scenarios as $scenario) {
            $resolver =  $resolverRegistry->resolve($scenario);
            $resolver->normalize($scenario);
        }

        app(ResolutionEngine::class)->resolve($scenarios);
    }
}
