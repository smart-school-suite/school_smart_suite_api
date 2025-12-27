<?php

namespace App\Analytics\Projections\Enrollement;

use App\Constant\Analytics\Enrollment\EnrollmentKpiDefination;
use App\Models\Analytics\Enrollment\EnrollmentAnalyticSnapshot;
use App\Constant\Analytics\Enrollment\EnrollmentAnalyticsDimension;

class SnapshotProjector
{
    public static function project($event)
    {
        $definitions = EnrollmentKpiDefination::definitions();
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
                $dimensions
            );

            $dateDimensions = self::handleDateDimension($def['dimensions'], $payload);
            $filter = array_merge($filter, $dateDimensions);
            EnrollmentAnalyticSnapshot::raw(function ($collection) use ($filter, $count) {
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

    private static function handleDateDimension(array $dimensions, array $payload): array
    {
        $collectedDimensions = collect($dimensions);
        $currentDate = now();
        $dateDimensions = [];
        if ($collectedDimensions->contains(EnrollmentAnalyticsDimension::YEAR)) {
            $dateDimensions[EnrollmentAnalyticsDimension::YEAR] = $currentDate->year;
        }
        return $dateDimensions;
    }
}
