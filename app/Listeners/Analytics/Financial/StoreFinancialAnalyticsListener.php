<?php

namespace App\Listeners\Analytics\Financial;

use App\Events\Analytics\FinancialAnalyticsEvent;
use App\Models\Analytics\FinanceAnalyticEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\AnalyticsEvent;

class StoreFinancialAnalyticsListener implements ShouldQueue
{
    use InteractsWithQueue;
    public function handle(FinancialAnalyticsEvent $event): void
    {
        FinanceAnalyticEvent::create([
            'event_type' => $event->eventType(),
            'school_branch_id' => $event->payload()['school_branch_id'],
            'amount' => $event->amount(),
            'payload' => $event->payload(),
            'occurred_at' => $event->occurredAt(),
            'version' => $event->version(),
            'event_hash' => sha1(
                $event->eventType() .
                    $event->payload()['school_branch_id'].
                    $event->occurredAt().
                    json_encode($event->payload())
            ),
        ]);
    }
}
