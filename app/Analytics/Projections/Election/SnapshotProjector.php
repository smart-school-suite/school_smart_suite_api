<?php

namespace App\Analytics\Projections\Election;

use App\Constant\Analytics\Election\ElectionAnalyticsDefination;
use App\Models\Analytics\Election\ElectionAnalyticsSnapshot;
use Illuminate\Support\Facades\Log;

class SnapshotProjector
{
    public static function project($event): void
    {
        $definitions = ElectionAnalyticsDefination::definations();
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
            Log::info("Election Snapshot Inserted Successfully 1");
            ElectionAnalyticsSnapshot::raw(function ($collection) use ($filter, $count) {
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
            Log::info("Election Snapshot Inserted Successfully 2");
        }
    }
}
