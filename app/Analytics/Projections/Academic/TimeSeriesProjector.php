<?php

namespace App\Analytics\Projections\Academic;
use App\Constant\Analytics\Academic\AcademicKpiDefination;
use App\Analytics\Projections\Shared\TimeSeriesBucket;
use App\Models\Analytics\AcademicAnalyticTimeSeries;
class TimeSeriesProjector
{
    public static function project($event): void
    {
        $definitions = AcademicKpiDefination::definitions();

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

                AcademicAnalyticTimeSeries::raw(function ($collection) use ($filter, $count) {
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
