<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Resolution\Core;

use App\Constant\Action\AppActions;

class ResolutionEngine
{
    protected string $conflict = "conflict";
    protected string $dependency = "dependency";
    public function resolve(array &$scenarios)
    {
        foreach ($scenarios as $scenario) {
            $params = [
                "preserve_slot" => $scenario->decision->was_normalized ?
                    $scenario->decision->preserved_slot :
                    $scenario->decision->target_details,
                "scenario" => $scenario
            ];

            foreach ($scenario->resolutions as $resolution) {
                $resolver = app(ResolutionRegistry::class)->handle($resolution);
                $solution = $resolver->resolve($resolution, $params);

                $hasResolver = (bool) $resolver;
                $defaultProposal = $hasResolver ? [$solution] : [[]];

                if ($resolution->type === $this->conflict) {
                    $modOption = collect($resolution->options)->firstWhere("action", AppActions::MODIFY);
                    if ($modOption) {
                        $modOption->proposals = array_merge(
                            $modOption->proposals ?? [],
                            $defaultProposal
                        );
                    }
                }

                if ($resolution->type === $this->dependency) {
                    $resolution->options["proposals"] = array_merge(
                        $resolution->options["proposals"] ?? [],
                        $defaultProposal
                    );
                }
            }
        }
    }
}
