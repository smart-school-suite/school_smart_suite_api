<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Contracts;

use App\Schedular\ExamTimetable\DTO\BlockerDTO;
use App\Schedular\ExamTimetable\Helpers\GenerateId;

/**
 * Turns a raw blocker (an array with a 'key', its own fields and a
 * 'conflict') into a BlockerDTO. Each blocker names the fields that
 * describe what is in the way; the id is a hash of them.
 */
abstract class AbstractBlocker implements BlockerBuilder
{
    /** @return string[] fields of the raw blocker kept as its entity */
    abstract protected function fields(): array;

    public function build(array $blocker): BlockerDTO
    {
        $entity = array_filter(
            array_intersect_key($blocker, array_flip($this->fields())),
            fn($value) => $value !== null && $value !== []
        );

        // A raw 'type' says which way the rule failed; 'type' itself names the blocker.
        if (isset($entity['type'])) {
            $entity = ['reason' => $entity['type'], ...array_diff_key($entity, ['type' => true])];
        }

        $dto = new BlockerDTO();
        $dto->type = static::type();
        $dto->id = app(GenerateId::class)->generateId(['type' => static::type(), ...$entity]);
        $dto->entity = ['type' => static::type(), ...$entity];
        $dto->conflict = array_filter($blocker['conflict'] ?? []);

        return $dto;
    }
}
