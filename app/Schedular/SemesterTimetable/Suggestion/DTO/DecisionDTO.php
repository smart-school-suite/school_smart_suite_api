<?php

namespace App\Schedular\SemesterTimetable\Suggestion\DTO;

class DecisionDTO
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $type,
        public string $target_id,
        public string $target_type,
        public ?array  $target_details,
        public ?array $original_slot = null,
        public ?array $preserved_slot = null,
        public bool $was_normalized = false
    ) {
        //
    }
}
