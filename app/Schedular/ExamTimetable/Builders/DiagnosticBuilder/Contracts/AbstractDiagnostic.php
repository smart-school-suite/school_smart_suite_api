<?php

namespace App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Contracts;

use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Core\BlockerRegistry;
use App\Schedular\ExamTimetable\DTO\DiagnosticDTO;
use App\Schedular\ExamTimetable\Helpers\GenerateId;

/**
 * Turns a raw violation ({constraint_failed, blockers}) into a DiagnosticDTO:
 * the constraint that failed, with a stable id, plus its blockers.
 */
abstract class AbstractDiagnostic implements DiagnosticBuilder
{
    /** @return string[] fields of constraint_failed that identify the request */
    abstract protected function fields(): array;

    public function build(array $diagnostic): DiagnosticDTO
    {
        $failed = $diagnostic['constraint_failed'] ?? [];
        $details = array_filter(
            array_intersect_key($failed, array_flip($this->fields())),
            fn($value) => $value !== null && $value !== []
        );
        $id = app(GenerateId::class)->generateId(['type' => static::type(), ...$details]);

        $dto = new DiagnosticDTO();
        $dto->id = $id;
        $dto->constraint_failed = [
            'type' => static::type(),
            'id' => $id,
            'details' => $details,
        ];
        $dto->blockers = app(BlockerRegistry::class)->build($diagnostic['blockers'] ?? [])->all();

        return $dto;
    }
}
