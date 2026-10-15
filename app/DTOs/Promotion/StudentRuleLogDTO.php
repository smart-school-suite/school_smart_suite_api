<?php

namespace App\DTOs\Promotion;

use InvalidArgumentException;

final readonly class StudentRuleLogDTO
{
    /**
     * @param StudentRuleLogReasonDTO[] $reasons
     */
    public function __construct(
        public string $rule,
        public bool $passed,
        public array $reasons = [],
        public bool $needsReview = false,
    ) {
        foreach ($reasons as $reason) {
            if (!$reason instanceof StudentRuleLogReasonDTO) {
                throw new InvalidArgumentException(
                    'Every reason must be an instance of StudentRuleLogReasonDTO.'
                );
            }
        }
    }

    public function toArray(): array
    {
        return [
            'rule' => $this->rule,
            'passed' => $this->passed,
            'needs_review' => $this->needsReview,
            'reasons' => array_map(
                fn (StudentRuleLogReasonDTO $reason) => $reason->toArray(),
                $this->reasons
            ),
        ];
    }
}
