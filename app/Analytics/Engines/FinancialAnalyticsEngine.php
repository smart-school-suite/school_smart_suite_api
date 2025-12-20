<?php

namespace App\Analytics\Engines;
use App\Analytics\Projections\Financial\SnapshotProjector;
use App\Analytics\Projections\Financial\TimeSeriesProjector;
class FinancialAnalyticsEngine
{
    public static function project($event): void
    {
        SnapshotProjector::project($event);
        TimeSeriesProjector::project($event);
    }
}
