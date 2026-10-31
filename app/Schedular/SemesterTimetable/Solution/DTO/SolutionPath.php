<?php

namespace App\Schedular\SemesterTimetable\Solution\DTO;

final class SolutionPath
{
    public const ADJUST_REQUEST = 'adjust_request';
    public const CLEAR_OBSTACLES = 'clear_obstacles';
    public const KEEP_ONE = 'keep_one';
    public const SHARED_FIX = 'shared_fix';

    public readonly string $id;
    public readonly int $cost;

    public function __construct(
        public readonly string $day,
        public readonly string $severity,
        public readonly string $strategy,
        public readonly Issue $primary,
        public readonly array $issues,
        public readonly array $steps,
    ) {
        $this->cost = array_sum(array_map(fn(Remedy $remedy) => $remedy->cost, $steps));
        $this->id = md5($strategy . '|' . $primary->id() . '|' . $this->signature());
    }

    public function signature(): string
    {
        $ids = array_map(fn(Remedy $remedy) => $remedy->id, $this->steps);
        sort($ids);

        return implode(',', $ids);
    }

    public function resolvedCount(): int
    {
        return count($this->issues);
    }

    public function toArray(): array
    {
        $steps = [];
        foreach (array_values($this->steps) as $index => $remedy) {
            $steps[] = $remedy->toArray($index + 1);
        }

        return [
            'id' => $this->id,
            'day' => $this->day,
            'severity' => $this->severity,
            'strategy' => $this->strategy,
            'issue_ids' => array_map(fn(Issue $issue) => $issue->id(), $this->issues),
            'decision' => [
                'type' => $this->strategy === self::KEEP_ONE ? 'keep' : 'fix',
                'target_id' => $this->primary->id(),
                'target_type' => $this->primary->type(),
                'target_details' => $this->primary->subject->details,
            ],
            'steps' => $steps,
            'step_count' => count($steps),
            'cost' => $this->cost,
            'summary' => implode('; ', array_map(fn(Remedy $remedy) => $remedy->label, $this->steps)),
        ];
    }
}
