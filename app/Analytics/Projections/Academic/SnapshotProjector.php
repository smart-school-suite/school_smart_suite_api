<?php

namespace App\Analytics\Projections\Academic;
use App\Constant\Analytics\Academic\AcademicKpiDefination;
use App\Models\Analytics\Academic\AcademicAnalyticSnapshot;

class SnapshotProjector
{
   public static function project($event) {
       $definitions = AcademicKpiDefination::definitions();
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

            AcademicAnalyticSnapshot::raw(function ($collection) use ($filter, $count) {
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
