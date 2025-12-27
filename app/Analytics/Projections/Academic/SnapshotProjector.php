<?php

namespace App\Analytics\Projections\Academic;

use App\Constant\Analytics\Academic\AcademicKpiDefination;
use App\Models\Analytics\Academic\AcademicAnalyticSnapshot;
use App\Constant\Analytics\Academic\AcademicAnalyticsDimension;

class SnapshotProjector
{
    public static function project($event)
    {
        $definitions = AcademicKpiDefination::definitions();
        foreach ($definitions as $def) {
            if (!in_array($event->eventType(), $def['source_events'] ?? [], true)) {
                continue;
            }

            $payload = $event->payload();
            $value = $def['type'] === 'counter' ? 1 : ($payload['value'] ?? 0);

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
            AcademicAnalyticSnapshot::raw(function ($collection) use ($filter, $value) {
                return $collection->updateOne(
                    $filter,
                    [
                        '$inc' => [
                            'value'   => $value,
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
        if ($collectedDimensions->contains(AcademicAnalyticsDimension::YEAR)) {
            $dateDimensions[AcademicAnalyticsDimension::YEAR] = $currentDate->year;
        }
        return $dateDimensions;
    }
}
