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
            //additional fee paid definitions
            FinancialAnalyticsKpi::ADDITIONAL_FEE_PAID => [
                'kpi' => FinancialAnalyticsKpi::ADDITIONAL_FEE_PAID,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
                    FinancialAnalyticsDimension::DEPARTMENT_ID,
                    FinancialAnalyticsDimension::CATEGORY_ID,
                    FinancialAnalyticsDimension::SPECIALTY_ID,
                    FinancialAnalyticsDimension::LEVEL_ID,
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

            //additional fee incurred definitions
            FinancialAnalyticsKpi::ADDITIONAL_FEE_INCURRED => [
                'kpi' => FinancialAnalyticsKpi::ADDITIONAL_FEE_INCURRED,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
                    FinancialAnalyticsDimension::DEPARTMENT_ID,
                    FinancialAnalyticsDimension::CATEGORY_ID,
                    FinancialAnalyticsDimension::SPECIALTY_ID,
                    FinancialAnalyticsDimension::LEVEL_ID,
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

            //tuition fee paid definitions

            FinancialAnalyticsKpi::TUITION_FEE_PAID => [
                'kpi' => FinancialAnalyticsKpi::TUITION_FEE_PAID,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
                    FinancialAnalyticsDimension::LEVEL_ID,
                    FinancialAnalyticsDimension::DEPARTMENT_ID,
                    FinancialAnalyticsDimension::SPECIALTY_ID,
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

            //tuition fee incurred definitions
            FinancialAnalyticsKpi::TUITION_FEE_INCURRED => [
                'kpi' => FinancialAnalyticsKpi::TUITION_FEE_INCURRED,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
                    FinancialAnalyticsDimension::DEPARTMENT_ID,
                    FinancialAnalyticsDimension::LEVEL_ID,
                    FinancialAnalyticsDimension::SPECIALTY_ID,
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::TUITION_FEE_INCURRED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive_on_paid',
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['hour', 'day', 'month'],
                ],
            ],

            //registration fee incurred definitions
            FinancialAnalyticsKpi::REGISTRATION_FEE_INCURRED => [
                'kpi' => FinancialAnalyticsKpi::REGISTRATION_FEE_INCURRED,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
                    FinancialAnalyticsDimension::DEPARTMENT_ID,
                    FinancialAnalyticsDimension::LEVEL_ID,
                    FinancialAnalyticsDimension::SPECIALTY_ID,
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

            //registration fee paid definitions
            FinancialAnalyticsKpi::REGISTRATION_FEE_PAID => [
                'kpi' => FinancialAnalyticsKpi::REGISTRATION_FEE_PAID,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
                    FinancialAnalyticsDimension::DEPARTMENT_ID,
                    FinancialAnalyticsDimension::SPECIALTY_ID,
                    FinancialAnalyticsDimension::LEVEL_ID,
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

            //resit fee paid definitions
            FinancialAnalyticsKpi::RESIT_FEE_PAID => [
                'kpi' => FinancialAnalyticsKpi::RESIT_FEE_PAID,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
                    FinancialAnalyticsDimension::SPECIALTY_ID,
                    FinancialAnalyticsDimension::DEPARTMENT_ID,
                    FinancialAnalyticsDimension::LEVEL_ID,
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

            //resit fee incurred definitions
            FinancialAnalyticsKpi::RESIT_FEE_INCURRED => [
                'kpi' => FinancialAnalyticsKpi::RESIT_FEE_INCURRED,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
                    FinancialAnalyticsDimension::SPECIALTY_ID,
                    FinancialAnalyticsDimension::LEVEL_ID,
                    FinancialAnalyticsDimension::DEPARTMENT_ID,
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

            //expense definitions
            FinancialAnalyticsKpi::EXPENSE_INCURRED => [
                'kpi' => FinancialAnalyticsKpi::EXPENSE_INCURRED,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::MONTH,
                    FinancialAnalyticsDimension::CATEGORY_ID,
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::EXPENSE_INCURRED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive_on_incurred',
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['hour', 'day', 'month'],
                ],
            ]
        ];
    }
}
