<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Engine;

use App\Schedular\SemesterTimetable\Suggestion\Handlers\Registry\HandlerRegistry;
use App\Schedular\SemesterTimetable\Suggestion\State\SolutionState;

// use App\Schedular\SemesterTimetable\Suggestion\State\SolutionState;

class ScenarioBuilder
{
    protected HandlerRegistry $registry;
    public function __construct()
    {
        $this->registry = new HandlerRegistry();
    }

    public function build(array $groups, array $dependencies): array
    {
        // 1. generate base scenarios from conflicts
        $scenarios = $this->buildFromGroups($groups);

        // 2. enforce dependencies
        $scenarios = $this->applyDependencies($scenarios, $dependencies);

        // 3. validate scenarios
        return $this->validate($scenarios);
    }

    protected function buildFromGroups(array $groups): array
    {
        if (empty($groups)) {
            return [[]];
        }

        $scenarios = [];

        foreach ($groups as $group) {
            $groupScenarios = $this->buildGroup($group);

            $scenarios = empty($scenarios)
                ? $groupScenarios
                : $this->merge($scenarios, $groupScenarios);
        }

        return $scenarios;
    }

    protected function buildGroup(array $group): array
    {
        $scenarios = [];

        foreach ($group as $keepNode) {

            $others = array_filter($group, fn($n) => $n->id !== $keepNode->id);

            $combinations = [[]];

            foreach ($others as $node) {

                $handler = $this->registry->get($node->type);
                if (!$handler) continue;

                $options = $handler->generate($node);

                $newCombos = [];

                foreach ($combinations as $combo) {
                    foreach ($options as $option) {
                        $newCombos[] = array_merge($combo, [$option]);
                    }
                }

                $combinations = $newCombos;
            }

            foreach ($combinations as $combo) {
                $scenarios[] = array_merge([
                    [
                        'action' => 'keep',
                        'target' => $keepNode
                    ]
                ], $combo);
            }
        }

        return $scenarios;
    }

    protected function applyDependencies(array $scenarios, array $dependencies): array
    {
        $result = [];

        foreach ($scenarios as $scenario) {

            $expanded = [$scenario];

            foreach ($dependencies as $edge) {

                $sourceId = $edge->from->id;
                $blocker = $edge->to;

                $isSourceKept = collect($scenario)->contains(function ($s) use ($sourceId) {
                    return $s['action'] === 'keep' && $s['target']->id === $sourceId;
                });

                if (!$isSourceKept) continue;

                $handler = $this->registry->get($blocker->type);
                if (!$handler) continue;

                $options = $handler->generate($blocker);

                if ($handler->isExclusive()) {
                    // 🔴 forced fix
                    foreach ($expanded as &$s) {
                        $s[] = $options[0];
                    }
                } else {
                    // 🟢 branching
                    $newExpanded = [];

                    foreach ($expanded as $s) {
                        foreach ($options as $opt) {
                            $newExpanded[] = array_merge($s, [$opt]);
                        }
                    }

                    $expanded = $newExpanded;
                }
            }

            foreach ($expanded as $s) {
                $result[] = $s;
            }
        }

        return $result;
    }
    protected function merge(array $a, array $b): array
    {
        $merged = [];

        foreach ($a as $x) {
            foreach ($b as $y) {
                $merged[] = array_merge($x, $y);
            }
        }

        return $merged;
    }
    protected function validate(array $scenarios): array
    {
        $valid = [];

        foreach ($scenarios as $scenario) {

            $state = new SolutionState();
            $ok = true;

            foreach ($scenario as $change) {

                if (!$state->canApply($change)) {
                    $ok = false;
                    break;
                }

                $state->apply($change);
            }

            if ($ok) {
                $valid[] = $scenario;
            }
        }

        return $valid;
    }
}
