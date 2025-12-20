<?php

namespace App\Listeners\Analytics\Financial;

use App\Analytics\Engines\FinancialAnalyticsEngine;
use App\Events\Analytics\FinancialAnalyticsEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProjectFinancialAnalyticsListener implements ShouldQueue
{
    use InteractsWithQueue;
    public function handle(FinancialAnalyticsEvent $event): void
    {
        FinancialAnalyticsEngine::project($event);
    }
}
