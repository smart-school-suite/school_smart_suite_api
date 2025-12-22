<?php

namespace App\Analytics\Engines;

use App\Analytics\Projections\Operational\SnapshotProjector;
use App\Analytics\Projections\Operational\TimeSeriesProjector;

class OperationalAnalyticsEngine
{
    public static function project($event): void
    {
        SnapshotProjector::project($event);
        TimeSeriesProjector::project($event);
    }
}
