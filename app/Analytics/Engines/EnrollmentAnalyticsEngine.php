<?php

namespace App\Analytics\Engines;
use App\Analytics\Projections\Enrollement\SnapshotProjector;
use App\Analytics\Projections\Enrollement\TimeSeriesProjector;
class EnrollmentAnalyticsEngine
{
   public static function project($event): void {
         SnapshotProjector::project($event);
         TimeSeriesProjector::project($event);
   }
}
