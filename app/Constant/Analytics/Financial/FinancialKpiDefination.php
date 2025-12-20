<?php

namespace App\Constant\Analytics\Financial;

use App\Constant\Analytics\Financial\FinancialAnalyticsDimension;
use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
use App\Constant\Analytics\Financial\FinancialAnalyticsEvent;

class FinancialKpiDefination
{
    public static function definitions(): array
    {
        return [

            FinancialAnalyticsKpi::REGISTRATION_FEE_PAID => [
                'kpi' => 'registration_fees_paid',
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::DEPARTMENT,
                    FinancialAnalyticsDimension::SPECIALTY,
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::REGISTRATION_FEE_PAID,
                    FinancialAnalyticsEvent::REGISTRATION_FEE_REVERSED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive_on_paid',
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['hour', 'day', 'month'],
                ],
            ],

            FinancialAnalyticsKpi::TUITION_FEE_PAID => [
                'kpi' => 'tuition_fees_paid',
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::DEPARTMENT,
                    FinancialAnalyticsDimension::SPECIALTY,

                ],
                'source_events' => [
                    FinancialAnalyticsEvent::TUITION_FEE_PAID,
                    FinancialAnalyticsEvent::TUITION_FEE_REVERSED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive_on_paid',
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['hour', 'day', 'month'],
                ],
            ],

            FinancialAnalyticsKpi::ADDITIONAL_FEE_PAID => [
                'kpi' => 'additional_fees_paid',
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::DEPARTMENT,
                    FinancialAnalyticsDimension::SPECIALTY,
                    FinancialAnalyticsDimension::CATEGORY,
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::ADDITIONAL_FEE_PAID,
                    FinancialAnalyticsEvent::ADDITIONAL_FEE_REVERSED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive_on_paid',
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['hour', 'day', 'month'],
                ],
            ],

            FinancialAnalyticsKpi::RESIT_FEE_PAID => [
                'kpi' => 'resit_fees_paid',
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::DEPARTMENT,
                    FinancialAnalyticsDimension::SPECIALTY,
                    FinancialAnalyticsDimension::COURSE,
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::RESIT_FEE_PAID,
                    FinancialAnalyticsEvent::RESIT_FEE_REVERSED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive_on_paid',
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['hour', 'day', 'month'],
                ],
            ],

            FinancialAnalyticsKpi::REGISTRATION_FEE_INCURRED => [
                'kpi' => 'registration_fees_incurred',
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::DEPARTMENT,
                    FinancialAnalyticsDimension::SPECIALTY,

                ],
                'source_events' => [
                    FinancialAnalyticsEvent::REGISTRATION_FEE_INCURRED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive',
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['hour', 'day', 'month'],
                ],
            ],

            FinancialAnalyticsKpi::TUITION_FEE_INCURRED => [
                'kpi' => 'tuition_fees_incurred',
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::DEPARTMENT,
                    FinancialAnalyticsDimension::SPECIALTY,

                ],
                'source_events' => [
                    FinancialAnalyticsEvent::TUITION_FEE_INCURRED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive',
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['hour', 'day', 'month'],
                ],
            ],

            FinancialAnalyticsKpi::ADDITIONAL_FEE_INCURRED => [
                'kpi' => 'additional_fees_incurred',
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::DEPARTMENT,
                    FinancialAnalyticsDimension::SPECIALTY,
                    FinancialAnalyticsDimension::CATEGORY,
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::ADDITIONAL_FEE_INCURRED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive',
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['hour', 'day', 'month'],
                ],
            ],

            FinancialAnalyticsKpi::RESIT_FEE_INCURRED => [
                'kpi' => 'resit_fees_incurred',
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::DEPARTMENT,
                    FinancialAnalyticsDimension::SPECIALTY,
                    FinancialAnalyticsDimension::COURSE,
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::RESIT_FEE_INCURRED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive',
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['hour', 'day', 'month'],
                ],
            ],
        ];
    }
}
