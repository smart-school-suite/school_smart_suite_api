<?php

namespace App\Analytics\Projections\Election;

use App\Analytics\Projections\Shared\TimeSeriesBucket;
use App\Constant\Analytics\Election\ElectionAnalyticsDefination;
use App\Models\Analytics\Election\ElectionAnalyticsTimeseries;

class TimeSeriesProjector
{
    public static function project($event): void
    {
        $definitions = ElectionAnalyticsDefination::definations();

        foreach ($definitions as $def) {
            if (!in_array($event->eventType(), $def['source_events'] ?? [], true)) {
                continue;
            }

            if (empty($def['time_series']['enabled'])) {
                continue;
            }

            $payload = $event->payload();

            $count = $def['type'] === 'counter' ? 1 : ($payload['value'] ?? 0);

            // Dimensions
            $dimensions = [];
            foreach ($def['dimensions'] as $dim) {
                $dimensions[$dim] = data_get($payload, $dim);
            }

            foreach ($def['time_series']['granularities'] as $granularity) {
                $bucket = TimeSeriesBucket::for(
                    $event->occurredAt(),
                    $granularity
                );

                $filter = array_merge(
                    [
                        'kpi'         => $def['kpi'],
                        'granularity' => $granularity,
                        'bucket'      => $bucket,
                    ],
                    $dimensions
                );

                ElectionAnalyticsTimeseries::raw(function ($collection) use ($filter, $count) {
                    return $collection->updateOne(
                        $filter,
                        [
                            '$inc' => [
                                'delta'   => $count,
                            ],
                        ],
                        ['upsert' => true]
                    );
                });
            }
        }
    }
}
