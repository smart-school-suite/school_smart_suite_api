<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Solution\Core;

use App\Constant\Action\AppActions;
use App\Schedular\SemesterTimetable\Suggestion\Solution\DTO\SolutionPathDTO;
use App\Schedular\SemesterTimetable\Suggestion\Solution\Handlers\Registry\SolutionHandlerRegistry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SolutionPathEngine
{
    protected const RESOLUTION_CONFLICT = "conflict";
    protected const RESOLUTION_DEPENDENCY = "dependency";
    public function generateSolutionPaths(array $scenarios): array
    {
        $paths = [];
        $solutionHandlerRegistry = app(SolutionHandlerRegistry::class);

        foreach ($scenarios as $scenario) {
            $steps = [];
            $optionsPool = collect([]);

            foreach ($scenario->resolutions as $index => $resolution) {
                $step = $index + 1;

                if ($resolution->type === self::RESOLUTION_CONFLICT) {
                    if ($index == 0) {
                        $options = [
                            ...$this->getModificationOptions($resolution),
                            ...$this->getRemoveOption($resolution)
                        ];
                    } else {
                        $options = [];
                        $resolvePaths = $this->generateResolvePaths($optionsPool);
                        $stepKey = "step_{$step}";
                        $optionsPool->put($stepKey, []);

                        foreach ($resolvePaths as $resolvePath) {
                            $handler = $solutionHandlerRegistry->getHandler($resolution->target_type);
                            $availableOptions = $handler->getSolution($resolution, $resolvePath);

                            if (collect($availableOptions)->isEmpty()) {
                                continue;
                            }

                            $options[] = [
                                "conditions" => collect($resolvePath)->pluck('option_id')->toArray(),
                                "options"    => $availableOptions
                            ];

                            $currentOptions = $optionsPool->get($stepKey, []);
                            $merged = array_merge($currentOptions, $availableOptions);
                            $uniqueOptions = $this->removeDuplicateOptions($merged);
                            $optionsPool->put($stepKey, $uniqueOptions);
                        }
                    }

                    // Populate pool for step 0 conflict (was missing before)
                    if ($index == 0) {
                        $optionsPool->put("step_{$step}", $options);
                    }

                    $steps[] = [
                        "step"            => $step,
                        "step_id"         => Str::uuid()->toString(),
                        "resolution_type" => $resolution->type,
                        "target_id"       => $resolution->target_id,
                        'target_type'     => $resolution->target_type,
                        'options'         => $options
                    ];
                } elseif ($resolution->type === self::RESOLUTION_DEPENDENCY) {
                    // ← this branch was completely missing for index > 0
                    $dependencyOptions = $this->getDependencyOptions($resolution);

                    $optionsPool->put("step_{$step}", $dependencyOptions); // ← always populate pool

                    $steps[] = [
                        "step"            => $step,
                        "step_id"         => Str::uuid()->toString(),
                        "resolution_type" => $resolution->type,
                        "target_id"       => $resolution->target_id,
                        'target_type'     => $resolution->target_type,
                        'options'         => $dependencyOptions
                    ];
                }
            }

            $paths[] = new SolutionPathDTO(
                scenarioId: $scenario->id,
                decision: (array) $scenario->decision,
                flow: $steps
            );
        }

        return $paths;
    }

    protected function removeDuplicateOptions(array $options): array
    {
        $seen = [];
        $unique = [];

        foreach ($options as $option) {
            $key = $option['option_id'] ?? null;

            if ($key === null) {
                $key = md5(json_encode($option));
            }

            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $unique[] = $option;
            }
        }

        return $unique;
    }
    protected function getDependencyOptions(object $resolution): array
    {
        $options = [];
        Log::info("Resolution", [$resolution->options]);
        foreach ($resolution->options['proposals'] as $proposal) {
            $options[] = [
                "type" => "dependency",
                'option_id' => $proposal["id"] ?? null,
                'action' => AppActions::MODIFY,
                'condition' => [],
                'params' => collect($proposal)->except('id')->toArray(),
            ];
        }
        return $options;
    }
    protected function getModificationOptions(object $resolution): array
    {
        $options = [];
        $modificationOptions = collect($resolution->options)->where("action", AppActions::MODIFY)->first();

        if ($modificationOptions) {
            foreach ($modificationOptions->proposals as $proposal) {
                $options[] = [
                    'option_id' => $proposal["id"] ?? null,
                    'action' => $modificationOptions->action,
                    'condition' => [],
                    'params' => collect($proposal)->except('id')->toArray(),
                ];
            }
        }

        return $options;
    }
    protected function getRemoveOption(object $resolution): ?array
    {
        $option = [];
        $removeOption = collect($resolution->options)->where("action", AppActions::REMOVE)->first();
        if ($removeOption) {
            $option[] = [
                'option_id' => $removeOption->proposals["id"] ?? null,
                'action' => $removeOption->action,
                'condition' => [],
                'params' => collect($removeOption->proposals)->except('id')->toArray(),
            ];
            return $option;
        }
        return $option;
    }
    protected function generateResolvePaths(Collection $optionsPool): array
    {
        $arrays = $optionsPool->values()->all();

        if (empty($arrays)) {
            return [];
        }

        $result = [[]];

        foreach ($arrays as $options) {
            $newResult = [];
            foreach ($result as $combination) {
                foreach ($options as $option) {
                    $newResult[] = array_merge($combination, [$option]);
                }
            }
            $result = $newResult;
        }

        return $result;
    }
}
