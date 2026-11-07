<?php

namespace App\Schedular\ExamTimetable\Solution\DTO;

/** Remedies that, applied together, fix one or more issues. */
final class SolutionPath
{
    public const ADJUST_REQUEST = 'adjust_request';
    public const CLEAR_OBSTACLES = 'clear_obstacles';
    public const KEEP_ONE = 'keep_one';
    public const SHARED_FIX = 'shared_fix';

    public readonly string $id;
    public readonly int $cost;
    /** true: re-running the scheduler with the steps applied fixes the issues and adds none; null: has manual steps. */
    public ?bool $verified = null;

    public function __construct(
        public readonly string $date,
        public readonly string $severity,
        public readonly string $strategy,
        public readonly Issue $primary,
        public readonly array $issues,
        public readonly array $steps,
        public readonly ?Subject $kept = null,
    ) {
        $this->cost = array_sum(array_map(fn(Remedy $remedy) => $remedy->cost, $steps));
        $this->id = substr(md5($strategy . '|' . ($kept?->id ?? $primary->id()) . '|' . $this->signature()), 0, 16);
    }

    public function withVerification(?bool $verified): self
    {
        $this->verified = $verified;
        return $this;
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

    public function hasManualSteps(): bool
    {
        foreach ($this->steps as $remedy) {
            if ($remedy->isManual()) {
                return true;
            }
        }
        return false;
    }

    /** @return array[] the steps as stored and applied */
    public function stepArrays(): array
    {
        $steps = [];
        foreach (array_values($this->steps) as $index => $remedy) {
            $steps[] = $remedy->toArray($index + 1);
        }
        return $steps;
    }

    public function toArray(): array
    {
        $steps = $this->stepArrays();

        return [
            'id' => $this->id,
            'date' => $this->date,
            'severity' => $this->severity,
            'strategy' => $this->strategy,
            'verified' => $this->verified,
            'issue_ids' => array_map(fn(Issue $issue) => $issue->id(), $this->issues),
            'decision' => [
                'type' => $this->strategy === self::KEEP_ONE ? 'keep' : 'fix',
                'target_id' => $this->kept?->id ?? $this->primary->id(),
                'target_type' => $this->kept?->type ?? $this->primary->type(),
                'target_details' => $this->kept?->details ?? $this->primary->subject->details,
            ],
            'steps' => $steps,
            'step_count' => count($steps),
            'cost' => $this->cost,
            'summary' => implode('; ', array_column($steps, 'label')),
        ];
    }
}
