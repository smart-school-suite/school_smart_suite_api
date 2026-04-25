<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Resolution\Core;

class ResolutionEngine
{
    protected string $conflict = "conflict";
    protected string $dependency = "dependency";
    public function resolve(array &$scenarios)
    {
        foreach ($scenarios as $scenario) {
            $params = [
                "perserve_slot" => $scenario->decision->preserved_slot,
                "scenario" => $scenario
            ];
            foreach ($scenario->resolutions as $resolution) {

                $resolver = app(ResolutionRegistry::class)->handle($resolution);
                $solution = $resolver->resolve($resolution, $params);
                if ($resolution->type === $this->conflict) {
                    $modOption = collect($resolution->options)->firstWhere("action", "modify");
                    $modOption->proposals = $solution;
                }
                if ($resolution->type === $this->dependency) {
                    $resolution->options->proposals = $solution;
                }
            }
        }
    }
}
