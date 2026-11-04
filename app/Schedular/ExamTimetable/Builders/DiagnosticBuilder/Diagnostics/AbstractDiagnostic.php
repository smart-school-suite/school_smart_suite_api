<?php

namespace App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics;

use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Core\BlockerRegistry;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Contracts\DiagnosticBuilder;
use App\Schedular\ExamTimetable\DTO\DiagnosticDTO;
use App\Schedular\SemesterTimetable\Helpers\GenerateId;

/**
 * Turns a raw violation into a DiagnosticDTO:
 * constraint_failed = {type, id, details}, blockers = BlockerDTO[].
 * The id is a hash of the identity fields only, so the same failure keeps its id between runs.
 */
abstract class AbstractDiagnostic implements DiagnosticBuilder
{
    /** Fields copied into details. */
    abstract protected function fields(): array;

    /** Fields that identify the failure (defaults to all fields). */
    protected function identity(): array
    {
        return $this->fields();
    }

    public function build(array $diagnostic): DiagnosticDTO
    {
        $failed = $diagnostic['constraint_failed'] ?? [];
        $details = [];

        foreach ($this->fields() as $field) {
            if (array_key_exists($field, $failed) && $failed[$field] !== null) {
                $details[$field] = $failed[$field];
            }
        }

        $identity = array_intersect_key($details, array_flip($this->identity()));

        $dto = new DiagnosticDTO();
        $dto->constraint_failed = [
            'type' => static::type(),
            'id' => app(GenerateId::class)->generateId(['type' => static::type(), ...$identity]),
            'details' => $details,
        ];
        $dto->id = $dto->constraint_failed['id'];
        $dto->blockers = app(BlockerRegistry::class)->build($diagnostic['blockers'] ?? [])->toArray();

        return $dto;
    }
}
