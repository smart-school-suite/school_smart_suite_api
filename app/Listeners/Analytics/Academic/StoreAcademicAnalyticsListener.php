<?php

namespace App\Listeners\Analytics\Academic;

use App\Events\Analytics\AcademicAnalyticsEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Analytics\AcademicAnalyticEvent;

class StoreAcademicAnalyticsListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    use InteractsWithQueue;

    public function handle(AcademicAnalyticsEvent $event): void
    {
        AcademicAnalyticEvent::create([
            'event_type' => $event->eventType(),
            'school_branch_id' => $event->payload()['school_branch_id'],
            'count' => $event->count(),
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
