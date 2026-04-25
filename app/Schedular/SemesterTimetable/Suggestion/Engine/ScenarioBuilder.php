<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Engine;

use App\Schedular\SemesterTimetable\Suggestion\DTO\DecisionDTO;
use App\Schedular\SemesterTimetable\Suggestion\DTO\ResolutionDTO;
use App\Schedular\SemesterTimetable\Suggestion\DTO\ScenarioDTO;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Registry\HandlerRegistry;
use App\Schedular\SemesterTimetable\Suggestion\Engine\DependencyExtractor;
use App\Schedular\SemesterTimetable\Suggestion\Normalization\ScenarioNormalizationEngine;

class ScenarioBuilder
{
    protected HandlerRegistry $registry;
    protected DependencyExtractor $dependencyExtractor;

    public function __construct()
    {
        $this->registry = new HandlerRegistry();
        $this->dependencyExtractor = new DependencyExtractor();
    }

    public function build(array $groups): array
    {
        $scenarios = [];

        foreach ($groups as $group) {

            if (count($group) === 1) {
                $scenario = $this->buildStandalone($group[0]);
                if ($scenario) $scenarios[] = $scenario;
                continue;
            }

            $scenarios = array_merge(
                $scenarios,
                $this->buildConflictGroup($group)
            );
        }

        app(ScenarioNormalizationEngine::class)->normalize($scenarios);
        return $scenarios;
    }

    protected function buildStandalone(array $constraint): ?ScenarioDTO
    {
        $handler = $this->registry->get($constraint['type']);
        if (!$handler) return null;

        $blockers = $constraint['blockers'];

        $resolutions = [];

        $depOptions = $handler->dependencyOptions($constraint, $blockers);

        foreach ($depOptions as $depOption) {
            $resolutions[] = new ResolutionDTO(
                'dependency',
                $depOption->blocker->id,
                $depOption->blocker->type,
                [
                    "field" => $depOption->field,
                    "type" => $depOption->type,
                    "reason" => $depOption->reason,
                    "proposals" => $depOption->proposals ?? []
                ],
                [
                    "blocker" => $depOption->blocker
                ]
            );
        }

        return new ScenarioDTO(
            id: uniqid('scenario_'),
            decision: new DecisionDTO(
                type:'fix_constraint',
                target_id: $constraint['id'],
                target_type : $constraint['type'],
                target_details : $constraint["details"]
            ),
            resolutions: $resolutions
        );
    }

    protected function buildConflictGroup(array $group): array
    {
        $scenarios = [];

        foreach ($group as $kept) {

            $handler = $this->registry->get($kept['type']);
            if (!$handler) continue;

            $resolutions = [];

            // 🔴 Resolve conflicts
            foreach ($group as $node) {

                if ($node['id'] === $kept['id']) continue;

                $h = $this->registry->get($node['type']);
                if (!$h) continue;

                $options = $h->conflictOptions($node);

                if (!empty($options)) {
                    $resolutions[] = new ResolutionDTO(
                        'conflict',
                        $node['id'],
                        $node['type'],
                        $options,
                        [
                            "blocker" => $node
                        ]
                    );
                }
            }

            // 🔵 Resolve dependencies
            $blockers = $this->dependencyExtractor->get($kept, $group);

            $depOptions = $handler->dependencyOptions($kept, $blockers);

            foreach ($depOptions as $depOption) {
                $resolutions[] = new ResolutionDTO(
                    'dependency',
                    $depOption->blocker->id,
                    $depOption->blocker->type,
                    [
                        "field" => $depOption->field,
                        "type" => $depOption->type,
                        "reason" => $depOption->reason,
                        "proposals" => $depOption->proposals ?? []
                    ],
                    [
                        "blocker" => $depOption->blocker
                    ]
                );
            }

            $scenarios[] = new ScenarioDTO(
                id: uniqid('scenario_'),
                decision: new DecisionDTO(
                   type: 'keep',
                   target_id: $kept['id'],
                   target_type: $kept['type'],
                   target_details: $kept['details']
                ),
                resolutions: $resolutions
            );
        }

        return $scenarios;
    }
}
