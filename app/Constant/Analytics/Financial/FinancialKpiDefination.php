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

            FinancialAnalyticsKpi::ADDITIONAL_FEE_PAID_DEPARTMENT => [
                'kpi' => FinancialAnalyticsKpi::ADDITIONAL_FEE_PAID_DEPARTMENT,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
                    FinancialAnalyticsDimension::DEPARTMENT_ID,
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::ADDITIONAL_FEE_PAID,
                    FinancialAnalyticsEvent::ADDITIONAL_FEE_REVERSED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive_on_paid',
                'time_series' => [
                    'enabled' => false,
                ],
            ],

            FinancialAnalyticsKpi::ADDITIONAL_FEE_PAID_SPECIALTY => [
                'kpi' => FinancialAnalyticsKpi::ADDITIONAL_FEE_PAID_SPECIALTY,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
                    FinancialAnalyticsDimension::SPECIALTY_ID,
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::ADDITIONAL_FEE_PAID,
                    FinancialAnalyticsEvent::ADDITIONAL_FEE_REVERSED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive_on_paid',
                'time_series' => [
                    'enabled' => false,
                ],
            ],

            FinancialAnalyticsKpi::ADDITIONAL_FEE_PAID_CATEGORY => [
                'kpi' => FinancialAnalyticsKpi::ADDITIONAL_FEE_PAID_CATEGORY,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
                    FinancialAnalyticsDimension::CATEGORY_ID,
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::ADDITIONAL_FEE_PAID,
                    FinancialAnalyticsEvent::ADDITIONAL_FEE_REVERSED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive_on_paid',
                'time_series' => [
                    'enabled' => false,
                ],
            ],

            //additional fee incurred definitions
            FinancialAnalyticsKpi::ADDITIONAL_FEE_INCURRED => [
                'kpi' => FinancialAnalyticsKpi::ADDITIONAL_FEE_INCURRED,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
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

            FinancialAnalyticsKpi::ADDITIONAL_FEE_INCURRED_DEPARTMENT => [
                'kpi' => FinancialAnalyticsKpi::ADDITIONAL_FEE_INCURRED_DEPARTMENT,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
                    FinancialAnalyticsDimension::DEPARTMENT_ID,
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::ADDITIONAL_FEE_PAID,
                    FinancialAnalyticsEvent::ADDITIONAL_FEE_REVERSED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive_on_paid',
                'time_series' => [
                    'enabled' => false,
                ],
            ],

            FinancialAnalyticsKpi::ADDITIONAL_FEE_INCURRED_SPECIALTY  => [
                'kpi' => FinancialAnalyticsKpi::ADDITIONAL_FEE_INCURRED_SPECIALTY,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
                    FinancialAnalyticsDimension::SPECIALTY_ID,
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::ADDITIONAL_FEE_PAID,
                    FinancialAnalyticsEvent::ADDITIONAL_FEE_REVERSED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive_on_paid',
                'time_series' => [
                    'enabled' => false,
                ],
            ],

            FinancialAnalyticsKpi::ADDITIONAL_FEE_INCURRED_CATEGORY => [
                'kpi' => FinancialAnalyticsKpi::ADDITIONAL_FEE_INCURRED_CATEGORY,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
                    FinancialAnalyticsDimension::CATEGORY_ID,
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::ADDITIONAL_FEE_PAID,
                    FinancialAnalyticsEvent::ADDITIONAL_FEE_REVERSED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive_on_paid',
                'time_series' => [
                    'enabled' => false,
                ],
            ],

            //tuition fee paid definitions

            FinancialAnalyticsKpi::TUITION_FEE_PAID => [
                'kpi' => FinancialAnalyticsKpi::TUITION_FEE_PAID,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID
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

            FinancialAnalyticsKpi::TUITION_FEE_PAID_DEPARTMENT => [
                'kpi' => FinancialAnalyticsKpi::TUITION_FEE_PAID_DEPARTMENT,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::DEPARTMENT_ID,
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::TUITION_FEE_PAID,
                    FinancialAnalyticsEvent::TUITION_FEE_REVERSED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive_on_paid',
                'time_series' => [
                    'enabled' => false
                ],
            ],

            FinancialAnalyticsKpi::TUITION_FEE_PAID_SPECIALTY => [
                'kpi' => FinancialAnalyticsKpi::TUITION_FEE_PAID_SPECIALTY,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::SPECIALTY_ID,
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::TUITION_FEE_PAID,
                    FinancialAnalyticsEvent::TUITION_FEE_REVERSED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive_on_paid',
                'time_series' => [
                    'enabled' => false
                ],
            ],

            FinancialAnalyticsKpi::TUITION_FEE_PAID_LEVEL => [
                'kpi' => FinancialAnalyticsKpi::TUITION_FEE_PAID_LEVEL,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::LEVEL_ID,
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::TUITION_FEE_PAID,
                    FinancialAnalyticsEvent::TUITION_FEE_REVERSED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive_on_paid',
                'time_series' => [
                    'enabled' => false
                ],
            ],

            //tuition fee incurred definitions
            FinancialAnalyticsKpi::TUITION_FEE_INCURRED => [
                'kpi' => FinancialAnalyticsKpi::TUITION_FEE_INCURRED,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID
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

            FinancialAnalyticsKpi::TUITION_FEE_INCURRED_DEPARTMENT => [
                'kpi' => FinancialAnalyticsKpi::TUITION_FEE_INCURRED_DEPARTMENT,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::DEPARTMENT_ID,
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::TUITION_FEE_INCURRED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive_on_paid',
                'time_series' => [
                    'enabled' => false
                ],
            ],

            FinancialAnalyticsKpi::TUITION_FEE_INCURRED_SPECIALTY => [
                'kpi' => FinancialAnalyticsKpi::TUITION_FEE_INCURRED_SPECIALTY,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::SPECIALTY_ID,
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::TUITION_FEE_INCURRED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive_on_paid',
                'time_series' => [
                    'enabled' => false
                ],
            ],

            FinancialAnalyticsKpi::TUITION_FEE_INCURRED_LEVEL => [
                'kpi' => FinancialAnalyticsKpi::TUITION_FEE_INCURRED_LEVEL,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::LEVEL_ID,
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::TUITION_FEE_INCURRED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive_on_paid',
                'time_series' => [
                    'enabled' => false
                ],
            ],

            //registration fee incurred definitions
            FinancialAnalyticsKpi::REGISTRATION_FEE_INCURRED => [
                'kpi' => FinancialAnalyticsKpi::REGISTRATION_FEE_INCURRED,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,

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

            FinancialAnalyticsKpi::REGISTRATION_FEE_INCURRED_DEPARTMENT => [
                'kpi' => FinancialAnalyticsKpi::REGISTRATION_FEE_INCURRED_DEPARTMENT,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::DEPARTMENT_ID,
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::REGISTRATION_FEE_INCURRED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive',
                'time_series' => [
                    'enabled' => false,
                ],
            ],

            FinancialAnalyticsKpi::REGISTRATION_FEE_INCURRED_SPECIALTY => [
                'kpi' => FinancialAnalyticsKpi::REGISTRATION_FEE_INCURRED_SPECIALTY,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::SPECIALTY_ID,
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::REGISTRATION_FEE_INCURRED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive',
                'time_series' => [
                    'enabled' => false,
                ],
            ],

            FinancialAnalyticsKpi::REGISTRATION_FEE_INCURRED_LEVEL => [
                'kpi' => FinancialAnalyticsKpi::REGISTRATION_FEE_INCURRED_LEVEL,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::LEVEL_ID,
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
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

            //registration fee paid definitions
            FinancialAnalyticsKpi::REGISTRATION_FEE_PAID => [
                'kpi' => FinancialAnalyticsKpi::REGISTRATION_FEE_PAID,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
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
            FinancialAnalyticsKpi::REGISTRATION_FEE_PAID_DEPARTMENT => [
                'kpi' => FinancialAnalyticsKpi::REGISTRATION_FEE_PAID_DEPARTMENT,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::DEPARTMENT_ID,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::REGISTRATION_FEE_PAID,
                    FinancialAnalyticsEvent::REGISTRATION_FEE_REVERSED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive_on_paid',
                'time_series' => [
                    'enabled' => false
                ],
            ],
            FinancialAnalyticsKpi::REGISTRATION_FEE_PAID_SPECIALTY => [
                'kpi' => FinancialAnalyticsKpi::REGISTRATION_FEE_PAID_SPECIALTY,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
                    FinancialAnalyticsDimension::SPECIALTY_ID,
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::REGISTRATION_FEE_PAID,
                    FinancialAnalyticsEvent::REGISTRATION_FEE_REVERSED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive_on_paid',
                'time_series' => [
                    'enabled' => false
                ],
            ],
            FinancialAnalyticsKpi::REGISTRATION_FEE_PAID_LEVEL => [
                'kpi' => FinancialAnalyticsKpi::REGISTRATION_FEE_PAID_LEVEL,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::LEVEL_ID,
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::REGISTRATION_FEE_PAID,
                    FinancialAnalyticsEvent::REGISTRATION_FEE_REVERSED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive_on_paid',
                'time_series' => [
                    'enabled' => false
                ],
            ],

            //resit fee paid definitions
            FinancialAnalyticsKpi::RESIT_FEE_PAID_LEVEL => [
                'kpi' => FinancialAnalyticsKpi::RESIT_FEE_PAID_LEVEL,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::LEVEL_ID,
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::RESIT_FEE_PAID,
                    FinancialAnalyticsEvent::RESIT_FEE_REVERSED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive_on_paid',
                'time_series' => [
                    'enabled' => false,
                ],
            ],
            FinancialAnalyticsKpi::RESIT_FEE_PAID => [
                'kpi' => FinancialAnalyticsKpi::RESIT_FEE_PAID,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
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
            FinancialAnalyticsKpi::RESIT_FEE_PAID_SPECIALTY => [
                'kpi' => FinancialAnalyticsKpi::RESIT_FEE_PAID_SPECIALTY,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::SPECIALTY_ID,
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::RESIT_FEE_PAID,
                    FinancialAnalyticsEvent::RESIT_FEE_REVERSED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive_on_paid',
                'time_series' => [
                    'enabled' => false
                ],
            ],
            FinancialAnalyticsKpi::RESIT_FEE_PAID_DEPARTMENT => [
                'kpi' => FinancialAnalyticsKpi::RESIT_FEE_PAID_DEPARTMENT,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::DEPARTMENT_ID,
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::RESIT_FEE_PAID,
                    FinancialAnalyticsEvent::RESIT_FEE_REVERSED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive_on_paid',
                'time_series' => [
                    'enabled' => false
                ],
            ],

            //resit fee incurred definitions
            FinancialAnalyticsKpi::RESIT_FEE_INCURRED_LEVEL => [
                'kpi' => FinancialAnalyticsKpi::RESIT_FEE_INCURRED_LEVEL,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::LEVEL_ID,
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::RESIT_FEE_INCURRED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive',
                'time_series' => [
                    'enabled' => false,
                ],
            ],
            FinancialAnalyticsKpi::RESIT_FEE_INCURRED => [
                'kpi' => FinancialAnalyticsKpi::RESIT_FEE_INCURRED,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
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
            FinancialAnalyticsKpi::RESIT_FEE_INCURRED_SPECIALTY => [
                'kpi' => FinancialAnalyticsKpi::RESIT_FEE_INCURRED_SPECIALTY,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::SPECIALTY_ID,
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::RESIT_FEE_INCURRED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive',
                'time_series' => [
                    'enabled' => false,
                ],
            ],
            FinancialAnalyticsKpi::RESIT_FEE_INCURRED_DEPARTMENT => [
                'kpi' => FinancialAnalyticsKpi::RESIT_FEE_INCURRED_DEPARTMENT,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::DEPARTMENT_ID,
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::RESIT_FEE_INCURRED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive',
                'time_series' => [
                    'enabled' => false,
                ],
            ],

            //expense definitions
            FinancialAnalyticsKpi::EXPENSE_INCURRED => [
                'kpi' => FinancialAnalyticsKpi::EXPENSE_INCURRED,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
                    FinancialAnalyticsDimension::YEAR,
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
            ],
            FinancialAnalyticsKpi::MONTH_EXPENSE_INCURRED => [
                'kpi' => FinancialAnalyticsKpi::MONTH_EXPENSE_INCURRED,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::MONTH,
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::EXPENSE_INCURRED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive_on_incurred',
                'time_series' => [
                    'enabled' => false,
                ],
            ],
            FinancialAnalyticsKpi::EXPENSE_CATEGORY_INCURRED => [
                'kpi' => FinancialAnalyticsKpi::EXPENSE_CATEGORY_INCURRED,
                'type' => 'counter',
                'dimensions' => [
                    FinancialAnalyticsDimension::SCHOOL_BRANCH_ID,
                    FinancialAnalyticsDimension::YEAR,
                    FinancialAnalyticsDimension::CATEGORY_ID,
                ],
                'source_events' => [
                    FinancialAnalyticsEvent::EXPENSE_INCURRED,
                ],
                'amount_path' => 'amount',
                'sign' => 'positive_on_incurred',
                'time_series' => [
                    'enabled' => false,
                ],
            ],
        ];
    }
}
