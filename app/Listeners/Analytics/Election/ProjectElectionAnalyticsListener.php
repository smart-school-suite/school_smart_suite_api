<?php

namespace App\Listeners\Analytics\Election;

use App\Events\Analytics\ElectionAnalyticsEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Analytics\Engines\ElectionAnalyticsEngine;

class ProjectElectionAnalyticsListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ElectionAnalyticsEvent $event): void
    {
        ElectionAnalyticsEngine::project($event);
    }
}
