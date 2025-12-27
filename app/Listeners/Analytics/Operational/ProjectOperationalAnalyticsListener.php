<?php

namespace App\Listeners\Analytics\Operational;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Analytics\Engines\OperationalAnalyticsEngine;
use App\Events\Analytics\OperationalAnalyticsEvent;

class ProjectOperationalAnalyticsListener implements ShouldQueue
{
    use InteractsWithQueue;
    public function handle(OperationalAnalyticsEvent $event): void
    {
        OperationalAnalyticsEngine::project($event);
    }
}
