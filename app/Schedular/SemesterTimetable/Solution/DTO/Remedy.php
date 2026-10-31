<?php

namespace App\Schedular\SemesterTimetable\Solution\DTO;

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
        $this->id = md5(implode('|', [$action, $target->id, json_encode($result)]));
    }

    public function overlapsWith(Remedy $other): bool
    {
        if (!$this->occupies || !$other->occupies) {
            return false;
        }

        $a = $this->occupies;
        $b = $other->occupies;

        if ($a['day'] !== $b['day'] || !($a['start'] < $b['end'] && $a['end'] > $b['start'])) {
            return false;
        }

        $sharesTeacher = !empty($a['teacher_id']) && ($a['teacher_id'] === ($b['teacher_id'] ?? null));
        $sharesHall = !empty($a['hall_id']) && ($a['hall_id'] === ($b['hall_id'] ?? null));

        return $sharesTeacher || $sharesHall || !empty($a['exclusive']) || !empty($b['exclusive']);
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
