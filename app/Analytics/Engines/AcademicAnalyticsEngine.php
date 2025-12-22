<?php

namespace App\Analytics\Engines;
use App\Analytics\Projections\Academic\SnapshotProjector;
use App\Analytics\Projections\Academic\TimeSeriesProjector;
class AcademicAnalyticsEngine
{
    public static function projection($event){
        SnapshotProjector::project($event);
        TimeSeriesProjector::project($event);
    }
}
