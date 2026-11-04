<?php

namespace App\Schedular\ExamTimetable\Solution\DTO;

use App\Schedular\ExamTimetable\Support\Time;

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

    /**
     * @param array|null $occupies {date, start, end, students?, hall_id?, invigilator_id?} what the change takes up,
     *                             so two changes that would clash are never combined
     */
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

    public function isManual(): bool
    {
        return $this->action === self::REVIEW;
    }

    public function overlapsWith(Remedy $other): bool
    {
        $a = $this->occupies;
        $b = $other->occupies;

        if (!$a || !$b || $a['date'] !== $b['date'] || !Time::overlaps($a['start'], $a['end'], $b['start'], $b['end'])) {
            return false;
        }

        $students = !empty($a['students']) && !empty($b['students']);
        $hall = !empty($a['hall_id']) && $a['hall_id'] === ($b['hall_id'] ?? null);
        $invigilator = !empty($a['invigilator_id']) && $a['invigilator_id'] === ($b['invigilator_id'] ?? null);

        return $students || $hall || $invigilator;
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
