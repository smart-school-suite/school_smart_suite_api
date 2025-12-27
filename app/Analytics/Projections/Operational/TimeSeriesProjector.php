<?php

namespace App\Analytics\Projections\Operational;

use App\Constant\Analytics\Operational\OperationalKpiDefination;
use App\Models\Analytics\Operational\OperationalAnalyticTimeseries;
class TimeSeriesProjector
{
    public static function project($event): void
    {
        // Implementation for projecting operational analytics events
        $definitions = OperationalKpiDefination::definitions();
        foreach ($definitions as $def) {
            if (!in_array($event->eventType(), $def['source_events'] ?? [], true)) {
                continue;
            }

            $payload = $event->payload();
            $count = $def['type'] === 'counter' ? 1 : ($payload['value'] ?? 0);

            // Build dimensions
            $dimensions = [];
            foreach ($def['dimensions'] as $dim) {
                $dimensions[$dim] = data_get($payload, $dim);
            }

            $filter = array_merge(
                ['kpi' => $def['kpi']],
                $dimensions,
                ['timestamp' => $event->occurredAt()]
            );

            OperationalAnalyticTimeseries::raw(function ($collection) use ($filter, $count) {
                return $collection->updateOne(
                    $filter,
                    [
                        '$inc' => [
                            'value'   => $count,
                        ],
                    ],
                    ['upsert' => true]
                );
            });
        }
    }
}
