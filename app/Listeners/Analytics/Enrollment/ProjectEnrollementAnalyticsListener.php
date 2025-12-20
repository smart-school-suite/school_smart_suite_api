<?php

namespace App\Listeners\Analytics\Enrollment;

use App\Events\Analytics\EnrollmentAnalyticsEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Analytics\Engines\EnrollmentAnalyticsEngine;

class ProjectEnrollementAnalyticsListener implements ShouldQueue
{
    /**
     * Handle the event.
     */
    use InteractsWithQueue;
    public function handle(EnrollmentAnalyticsEvent $event): void
    {
        EnrollmentAnalyticsEngine::project($event);
    }
}
