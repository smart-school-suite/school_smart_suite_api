<?php

namespace App\Listeners\Analytics\Operational;

use App\Models\Analytics\OperationalAnalyticEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\Analytics\OperationalAnalyticsEvent;

class StoreOperationalAnalyticsListener implements ShouldQueue
{
    use InteractsWithQueue;
    public function handle(OperationalAnalyticsEvent $event): void
    {
        OperationalAnalyticEvent::create([
            'event_type' => $event->eventType(),
            'school_branch_id' => $event->payload()['school_branch_id'],
            'count' => $event->value(),
            'payload' => $event->payload(),
            'occurred_at' => $event->occurredAt(),
            'version' => $event->version(),
            'event_hash' => sha1(
                $event->eventType() .
                    $event->payload()['school_branch_id'] .
                    $event->occurredAt() .
                    json_encode($event->payload())
            ),
        ]);
    }
}
