<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Solution\Core;

use App\Constant\Action\AppActions;
use App\Schedular\SemesterTimetable\Suggestion\Solution\DTO\SolutionPathDTO;
use App\Schedular\SemesterTimetable\Suggestion\Solution\Handlers\Registry\SolutionHandlerRegistry;
use Illuminate\Support\Collection;

class SolutionPathEngine
{
    public function generateSolutionPaths(array $scenarios): array
    {
        $paths = [];

        foreach ($scenarios as $scenario) {
            $scenarioPaths = $this->generatePathsForScenario($scenario);
            $paths = array_merge($paths, $scenarioPaths);
        }

        return $paths;
    }
    protected function generatePathsForScenario(object $scenario): array
    {
        $resolutions = collect($scenario->resolutions);

        if ($resolutions->isEmpty()) {
            return [$this->createEmptyPath($scenario)];
        }

        $allPaths = [];
        $this->buildConditionalTree(
            resolutions: $resolutions,
            scenario: $scenario,
            currentPath: [],
            currentIndex: 0,
            resolvedSteps: [],
            allPaths: $allPaths
        );

        return $allPaths;
    }
    protected function buildConditionalTree(
        Collection $resolutions,
        object $scenario,
        array $currentPath,
        int $currentIndex,
        array $resolvedSteps,
        array &$allPaths
    ): void {

        if ($currentIndex >= $resolutions->count()) {
            $allPaths[] = $this->createPathDTO($scenario, $currentPath);
            return;
        }

        $currentResolution = $resolutions[$currentIndex];
        $handler = app(SolutionHandlerRegistry::class)->getHandler($currentResolution->target_type);

        // Get available options from handler based on resolved steps
        $availableOptions = $handler ?
            $handler->getSolution($currentResolution, $resolvedSteps) :
            $this->getAllOptions($currentResolution);

        if (empty($availableOptions)) {
            return; // No options available, path dies
        }

        // Build step with conditional options
        $step = [
            'step' => $currentIndex + 1,
            'step_id' => $this->generateStepId($currentIndex, $currentResolution->type),
            'resolution_type' => $currentResolution->type,
            'target_id' => $currentResolution->target_id,
            'target_type' => $currentResolution->target_type,
        ];

        // Add depends_on if there are previous steps
        if (!empty($resolvedSteps)) {
            $step['depends_on'] = $this->getDependencies($resolvedSteps);
        }

        // Group options by their conditions
        $step['options'] = $this->buildConditionalOptions($availableOptions, $resolvedSteps);

        // For each possible selection, create a branch
        foreach ($availableOptions as $option) {
            $selectedOptionId = $option['option_id'];
            $newResolvedSteps = array_merge($resolvedSteps, [$selectedOptionId]);

            // Recursively build the rest of the tree
            $this->buildConditionalTree(
                resolutions: $resolutions,
                scenario: $scenario,
                currentPath: array_merge($currentPath, [$step]),
                currentIndex: $currentIndex + 1,
                resolvedSteps: $newResolvedSteps,
                allPaths: $allPaths
            );
        }
    }
    protected function buildConditionalOptions(array $availableOptions, array $resolvedSteps): array
    {
        $conditionalOptions = [];

        // Group options by their condition patterns
        $groupedByCondition = [];

        foreach ($availableOptions as $option) {
            $conditionKey = $this->getConditionKey($option, $resolvedSteps);

            if (!isset($groupedByCondition[$conditionKey])) {
                $groupedByCondition[$conditionKey] = [
                    'condition' => $option['condition'] ?? [],
                    'options' => []
                ];
            }

            $groupedByCondition[$conditionKey]['options'][] = [
                'option_id' => $option['option_id'],
                'action' => $option['action'],
                'params' => $option['params'] ?? [],
            ];
        }

        // Convert to array format
        foreach ($groupedByCondition as $group) {
            $conditionalOptions[] = $group;
        }

        return $conditionalOptions;
    }
    protected function getConditionKey(array $option, array $resolvedSteps): string
    {
        if (isset($option['condition']) && !empty($option['condition'])) {
            return implode('|', $option['condition']);
        }
        return 'default';
    }
    protected function getDependencies(array $resolvedSteps): array
    {
        $dependencies = [];

        foreach ($resolvedSteps as $index => $stepId) {
            $dependencies[] = $this->generateStepId($index, 'conflict');
        }

        return $dependencies;
    }
    protected function getAllOptions(object $resolution): array
    {
        $options = [];
        if ($resolution->type === "conflict") {
            $modificationOptions = collect($resolution->options)->where("action", AppActions::MODIFY)->first();
            if ($modificationOptions) {
                foreach ($modificationOptions->proposals as $proposal) {
                    $options[] = [
                        "type" => "conflict",
                        'option_id' => $proposal["id"] ?? null,
                        'action' => $modificationOptions->action,
                        'condition' => [],
                        'params' => collect($proposal)->except('id')->toArray(),
                    ];
                }
            }

            $removeOption = collect($resolution->options)->where("action", AppActions::REMOVE)->first();
            if ($removeOption) {
                $options[] = [
                    "type" => "conflict",
                    'option_id' => $removeOption->proposals["id"] ?? null,
                    'action' => $removeOption->action,
                    'condition' => [],
                    'params' => collect($removeOption->proposals)->except('id')->toArray(),
                ];
            }
        }
        if ($resolution->type === "dependency") {
            foreach ($resolution->options['proposals'] as $proposal) {
                $options[] = [
                    "type" => "dependency",
                    'option_id' => $proposal["id"] ?? null,
                    'action' => AppActions::MODIFY,
                    'condition' => [],
                    'params' => collect($proposal)->except('id')->toArray(),
                ];
            }
        }

        return $options;
    }
    protected function createPathDTO(object $scenario, array $flow): SolutionPathDTO
    {
        return new SolutionPathDTO(
            scenarioId: $scenario->id,
            decision: (array) $scenario->decision,
            flow: $flow
        );
    }
    protected function createEmptyPath(object $scenario): SolutionPathDTO
    {
        return new SolutionPathDTO(
            scenarioId: $scenario->id,
            decision: (array) $scenario->decision,
            flow: []
        );
    }
    protected function generateStepId(int $index, string $type): string
    {
        $letter = $index < 26 ? chr(65 + $index) : 'Z' . ($index - 25);

        if ($type === 'conflict') {
            return "step_{$letter}";
        }

        return "step_dep_{$letter}";
    }
}
