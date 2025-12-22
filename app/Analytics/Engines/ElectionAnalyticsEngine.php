<?php

namespace App\Analytics\Engines;

use App\Analytics\Projections\Election\SnapshotProjector;
use App\Analytics\Projections\Election\TimeSeriesProjector;

class ElectionAnalyticsEngine
{
    public static function project($event): void
    {
        SnapshotProjector::project($event);
        TimeSeriesProjector::project($event);
    }
}
