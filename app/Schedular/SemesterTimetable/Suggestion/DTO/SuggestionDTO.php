<?php

namespace App\Schedular\SemesterTimetable\Suggestion\DTO;

use App\Schedular\SemesterTimetable\Suggestion\Graph\Node;

class SuggestionDTO
{
    public function __construct(
        public string $action, // keep | remove | modify
        public Node $target,
        public array $changes = [], // array<ChangeDTO>
        public string $label = ''
    ) {}

    public function toArray(): array
    {
        return [
            'action' => $this->action,
            'target' => [
                'id' => $this->target->id,
                'type' => $this->target->type,
            ],
            'changes' => array_map(fn($c) => $c->toArray(), $this->changes),
            'label' => $this->label,
        ];
    }
}
