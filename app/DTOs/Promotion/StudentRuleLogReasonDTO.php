<?php

namespace App\DTOs\Promotion;


final readonly class StudentRuleLogReasonDTO
{
    public function __construct(
        public string $reason,
        public array $details = [],
    ) {}

    public function toArray(): array
    {
        return [
            'reason' => $this->reason,
            'details' => $this->details,
        ];
    }
}
