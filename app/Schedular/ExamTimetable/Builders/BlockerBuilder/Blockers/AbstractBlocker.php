<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers;

use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Contracts\BlockerBuilder;
use App\Schedular\ExamTimetable\DTO\BlockerDTO;
use App\Schedular\SemesterTimetable\Helpers\GenerateId;

/**
 * Turns a raw blocker into a BlockerDTO: entity is what is in the way, conflict is the session it blocks.
 * The id hashes the type and entity, so one obstacle shared by several failures has one id.
 */
abstract class AbstractBlocker implements BlockerBuilder
{
    /** Fields that identify the obstacle; other fields become evidence. */
    abstract protected function identity(): array;

    public function build(array $blocker): BlockerDTO
    {
        $fields = collect($blocker)->except(['key', 'conflict'])->filter(fn($value) => $value !== null);
        $entity = $fields->only($this->identity())->all();

        $dto = new BlockerDTO();
        $dto->type = static::type();
        $dto->id = app(GenerateId::class)->generateId(['type' => static::type(), ...$entity]);
        $dto->entity = ['type' => static::type(), ...$entity];
        $dto->evidence = $fields->except($this->identity())->all();
        $dto->conflict = array_filter($blocker['conflict'] ?? [], fn($value) => $value !== null);

        return $dto;
    }
}
