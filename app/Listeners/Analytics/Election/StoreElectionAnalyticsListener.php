<?php

namespace App\Listeners\Analytics\Election;

use App\Events\Analytics\ElectionAnalyticsEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Analytics\Election\ElectionAnalyticEvent;

class StoreElectionAnalyticsListener implements ShouldQueue
{
    use InteractsWithQueue;
    public function handle(ElectionAnalyticsEvent $event): void
    {
        ElectionAnalyticEvent::create([
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
