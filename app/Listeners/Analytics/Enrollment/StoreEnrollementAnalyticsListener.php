<?php

namespace App\Listeners\Analytics\Enrollment;

use App\Events\Analytics\EnrollmentAnalyticsEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Analytics\EnrollmentAnalyticEvent;

class StoreEnrollementAnalyticsListener implements ShouldQueue
{
   use InteractsWithQueue;
    public function handle(EnrollmentAnalyticsEvent $event): void
    {
        EnrollmentAnalyticEvent::create([
            'event_type' => $event->eventType(),
            'school_branch_id' => $event->payload()['school_branch_id'],
            'amount' => $event->value(),
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
