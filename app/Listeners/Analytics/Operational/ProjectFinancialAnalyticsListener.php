<?php

namespace App\Listeners\Analytics\Operational;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Analytics\Engines\OperationalAnalyticsEngine;

class ProjectFinancialAnalyticsListener implements ShouldQueue
{
    use InteractsWithQueue;
    public function handle(object $event): void
    {
        OperationalAnalyticsEngine::project($event);
    }
}
