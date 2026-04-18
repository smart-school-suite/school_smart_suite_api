<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Engine;

use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionDTO;
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
        $scenarios = [];

        foreach ($groups as $group) {
            $scenarios = array_merge($scenarios, $this->buildGroup($group));
        }

        return $this->applyDependencies($scenarios, $dependencies);
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

                $allowed = $handler->allowedActions();

                $options = [];

                if (in_array('remove', $allowed)) {
                    $options[] = new SuggestionDTO(
                        action: 'remove',
                        target: $node,
                        label: 'Remove conflicting constraint'
                    );
                }

                if (in_array('modify', $allowed)) {
                    $result = $handler->generate($node);
                    $options = array_merge($options, $result['modify_self'] ?? []);
                }

                if (empty($options)) {
                    $combinations = [];
                    break;
                }

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
                    new SuggestionDTO('keep', $keepNode)
                ], $combo);
            }
        }

        return $scenarios;
    }
    protected function applyDependencies(array $scenarios, array $dependencies): array
    {
        $final = [];

        foreach ($scenarios as $scenario) {

            $keptNodes = collect($scenario)
                ->filter(fn($s) => $s->action === 'keep')
                ->map(fn($s) => $s->target);

            foreach ($keptNodes as $node) {

                $blockers = [];

                foreach ($dependencies as $edge) {
                    if ($edge->from->id === $node->id) {
                        $blockers[] = $edge->to;
                    }
                }

                $handler = $this->registry->get($node->type);
                if (!$handler) continue;

                $result = $handler->generate($node, $blockers);

                // 🟢 Resolve blockers
                foreach ($result['resolve_blockers'] ?? [] as $fix) {
                    $final[] = array_merge($scenario, [$fix]);
                }

                // 🔵 Modify self (skip blockers)
                foreach ($result['modify_self'] ?? [] as $mod) {

                    $filtered = array_filter(
                        $scenario,
                        fn($s) => $s->target->id !== $node->id
                    );

                    $final[] = array_merge($filtered, [$mod]);
                }
            }
        }

        return $final;
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
