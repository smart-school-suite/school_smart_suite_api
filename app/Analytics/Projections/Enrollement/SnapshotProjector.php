<?php

namespace App\Analytics\Projections\Enrollement;
use App\Constant\Analytics\Enrollment\EnrollmentKpiDefination;
use App\Models\Analytics\EnrollmentAnalyticsSnapshot;
class SnapshotProjector
{
   public static function project($event) {
       $definitions = EnrollmentKpiDefination::definitions();
             foreach ($definitions as $def) {
            if (!in_array($event->eventType(), $def['source_events'] ?? [], true)) {
                continue;
            }

            $payload = $event->payload();
            $count = $def['type'] === 'counter' ? 1 : ($payload['count'] ?? 0);

            // Build dimensions
            $dimensions = [];
            foreach ($def['dimensions'] as $dim) {
                $dimensions[$dim] = data_get($payload, $dim);
            }

            $filter = array_merge(
                ['kpi' => $def['kpi']],
                $dimensions
            );

            EnrollmentAnalyticsSnapshot::raw(function ($collection) use ($filter, $count) {
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
