<?php

namespace App\Analytics\Projections\Academic;

use App\Constant\Analytics\Academic\AcademicKpiDefination;
use App\Constant\Analytics\Academic\AcademicAnalyticsDimension;
use App\Analytics\Projections\Shared\TimeSeriesBucket;
use App\Models\Analytics\Academic\AcademicAnalyticTimeSeries;

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

            $value = $def['type'] === 'counter' ? 1 : ($payload['value'] ?? 0);

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
                $dateDimensions = self::handleDateDimension($def['dimensions'], $payload);
                $filter = array_merge($filter, $dateDimensions);
                AcademicAnalyticTimeSeries::raw(function ($collection) use ($filter, $value) {
                    return $collection->updateOne(
                        $filter,
                        [
                            '$inc' => [
                                'delta'   => $value,
                            ],
                        ],
                        ['upsert' => true]
                    );
                });
            }
        }
    }
    private static function handleDateDimension(array $dimensions, array $payload): array
    {
        $collectedDimensions = collect($dimensions);
        $currentDate = now();
        $dateDimensions = [];
        if ($collectedDimensions->contains(AcademicAnalyticsDimension::YEAR)) {
            $dateDimensions[AcademicAnalyticsDimension::YEAR] = $currentDate->year;
        }
        return $dateDimensions;
    }
}
