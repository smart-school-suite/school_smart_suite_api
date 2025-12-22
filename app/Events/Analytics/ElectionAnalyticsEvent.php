<?php

namespace App\Events\Analytics;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Carbon\CarbonImmutable;
class ElectionAnalyticsEvent implements ShouldQueue
{
    use Dispatchable,  SerializesModels;

    private string $eventType;
    private array $payload;
    private CarbonImmutable $occurredAt;
    private int  $version;
    public function __construct(
        string $eventType,
        array $payload,
        int $version,
        ?CarbonImmutable $occurredAt = null
    ) {
        $this->eventType  = $eventType;
        $this->payload    = $payload;
        $this->version = $version;
        $this->occurredAt = $occurredAt ?? CarbonImmutable::now();
    }

    /* ───────────── ACCESSORS ───────────── */

    public function version(): int
    {
        return $this->version;
    }
    public function eventType(): string
    {
        return $this->eventType;
    }

    public function payload(): array
    {
        return $this->payload;
    }

    public function occurredAt(): CarbonImmutable
    {
        return $this->occurredAt;
    }

    public function schoolBranchId(): string {
         return $this->payload['school_branch_id'];
    }

    /**
     * Standardized monetary accessor
     */
    public function value(): float
    {
        return (int) ($this->payload['value'] ?? 0);
    }
}
