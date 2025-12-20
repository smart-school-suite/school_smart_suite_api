<?php

namespace App\Analytics\Projections\Financial;

use App\Constant\Analytics\Financial\FinancialKpiDefination;
use App\Analytics\Projections\Shared\TimeSeriesBucket;
use App\Models\Analytics\FinancialAnalyticsTimeSeries as AnalyticsTimeSeries;
use MongoDB\BSON\UTCDateTime;

class TimeSeriesProjector
{
    public static function project($event): void
    {
        $definitions = FinancialKpiDefination::definitions();

        foreach ($definitions as $def) {
            if (!in_array($event->eventType(), $def['source_events'] ?? [], true)) {
                continue;
            }

            if (empty($def['time_series']['enabled'])) {
                continue;
            }

            $payload = $event->payload();

            $amount = data_get($payload, $def['amount_path'] ?? 'amount', 0);

            // Handle refunds / reversals
            if (self::handleReversal($event->eventType())) {
                $amount = -abs($amount);
            }

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

                AnalyticsTimeSeries::raw(function ($collection) use ($filter, $amount) {
                    return $collection->updateOne(
                        $filter,
                        [
                            '$inc' => [
                                'delta'   => $amount,
                            ],
                        ],
                        ['upsert' => true]
                    );
                });
            }
        }
    }
    private static function handleReversal(string $eventType)
    {
        $reversalEvents = collect([
            'finance.registration_fee.reversed',
            'finance.tuition_fee.reversed',
            'finance.additional_fee.reversed',
            'finance.resit_fee.reversed',
        ]);
        return $reversalEvents->contains($eventType);
    }
}
