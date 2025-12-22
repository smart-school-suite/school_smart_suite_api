<?php

namespace App\Listeners\Analytics\Academic;

use App\Analytics\Engines\AcademicAnalyticsEngine;
use App\Events\Analytics\AcademicAnalyticsEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProjectAcademicAnalyticsListener Implements ShouldQueue
{
    use InteractsWithQueue;
    /**
     * Handle the event.
     */
    public function handle(AcademicAnalyticsEvent $event): void
    {
         AcademicAnalyticsEngine::projection($event);
    }
}
