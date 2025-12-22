<?php

namespace App\Analytics\Projections\Financial;

use App\Constant\Analytics\Financial\FinancialKpiDefination;
use App\Models\Analytics\Finance\FinanceAnalyticSnapshot as AnalyticsSnapshot;
use App\Constant\Analytics\Financial\FinancialAnalyticsEvent;

class SnapshotProjector
{
    public static function project($event): void
    {
        $definitions = FinancialKpiDefination::definitions();

        foreach ($definitions as $def) {
            if (!in_array($event->eventType(), $def['source_events'] ?? [], true)) {
                continue;
            }

            $payload = $event->payload();

            $amount = data_get($payload, $def['amount_path'] ?? 'amount', 0);

            if (self::handleReversal($event->eventType())) {
                $amount = -abs($amount);
            }

            // Build dimensions
            $dimensions = [];
            foreach ($def['dimensions'] as $dim) {
                $dimensions[$dim] = data_get($payload, $dim);
            }

            $filter = array_merge(
                ['kpi' => $def['kpi']],
                $dimensions
            );

            AnalyticsSnapshot::raw(function ($collection) use ($filter, $amount) {
                return $collection->updateOne(
                    $filter,
                    [
                        '$inc' => [
                            'value'   => $amount,
                        ],
                    ],
                    ['upsert' => true]
                );
            });
        }
    }

    private static function handleReversal(string $eventType){
         $reversalEvents = collect([
            FinancialAnalyticsEvent::REGISTRATION_FEE_REVERSED,
            FinancialAnalyticsEvent::TUITION_FEE_REVERSED,
            FinancialAnalyticsEvent::ADDITIONAL_FEE_REVERSED,
            FinancialAnalyticsEvent::RESIT_FEE_REVERSED,
         ]);
            return $reversalEvents->contains($eventType);
    }
}
