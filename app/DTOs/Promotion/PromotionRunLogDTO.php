<?php

namespace App\DTOs\Promotion;

use InvalidArgumentException;

final readonly class PromotionRunLogDTO
{
    /**
     * @param PromotionRunLogReasonDTO[] $reasons
     */
    public function __construct(
        public string $status,
        public string $title,
        public array $reasons = [],
    ) {
        foreach ($reasons as $reason) {
            if (!$reason instanceof PromotionRunLogReasonDTO) {
                throw new InvalidArgumentException(
                    'Every reason must be an instance of PromotionRunLogReasonDTO.'
                );
            }
        }
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'title' => $this->title,
            'reasons' => array_map(
                fn (PromotionRunLogReasonDTO $reason) => $reason->toArray(),
                $this->reasons
            ),
        ];
    }
}
