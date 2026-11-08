<?php

namespace App\Schedular\ExamTimetable\Solution\DTO;

/**
 * One concrete change to a request or an obstacle. Lower cost is better.
 * 'occupies' is the exam time a move would take, so two moves landing on
 * the same time are never combined in one path.
 */
final class Remedy
{
    public const MOVE = 'move';
    public const MODIFY = 'modify';
    public const REPLACE = 'replace';
    public const REMOVE = 'remove';
    public const RELAX = 'relax';
    public const REVIEW = 'review';

    public readonly string $id;
    public readonly array $changes;

    public function __construct(
        public readonly string $action,
        public readonly Subject $target,
        public readonly string $label,
        array $changes,
        public readonly array $result,
        public readonly int $cost,
        public readonly ?array $occupies = null,
    ) {
        $this->changes = array_values(array_filter($changes, fn(array $change) => ($change['from'] ?? null) !== ($change['to'] ?? null)));
        $this->id = substr(md5(implode('|', [$action, $target->id, json_encode($result)])), 0, 16);
    }

    public function isManual(): bool
    {
        return $this->action === self::REVIEW;
    }

    /** Two moves of the same students' exams onto overlapping times. */
    public function overlapsWith(Remedy $other): bool
    {
        $a = $this->occupies;
        $b = $other->occupies;

        if (!$a || !$b || $a['date'] !== $b['date']) {
            return false;
        }

        return $a['start'] < $b['end'] && $b['start'] < $a['end'];
    }

    public function toArray(int $step): array
    {
        return [
            'step' => $step,
            'id' => $this->id,
            'action' => $this->action,
            'label' => $this->label,
            'target' => $this->target->toArray(),
            'changes' => $this->changes,
            'result' => $this->result,
            'cost' => $this->cost,
        ];
    }
}
