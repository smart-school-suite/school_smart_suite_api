<?php

namespace App\Schedular\ExamTimetable\DTO;

class DiagnosticDTO
{
    public string $id;
    public array $constraint_failed = [];
    /** @var BlockerDTO[] */
    public array $blockers = [];
    public array $suggestions = [];
    public function __construct() {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'constraint_failed' => $this->constraint_failed,
            'blockers' => array_map(fn(BlockerDTO $blocker) => (array) $blocker, $this->blockers),
            'suggestions' => $this->suggestions,
        ];
    }
}
