<?php

namespace App\Constant\Analytics\Operational;

class OperationalKpiDefination
{
   public static function definitions(): array {
    return [
        OperationalAnalyticsKpi::SPECIALTY => [
            'kpi' => OperationalAnalyticsKpi::SPECIALTY,
            'type' => 'counter',
            'dimensions' => [
                OperationalAnalyticsDimension::LEVEL_ID,
                OperationalAnalyticsDimension::SCHOOL_BRANCH_ID
            ],
            'source_events' => [
                OperationalAnalyticsEvent::COURSE_CREATED,

            ],
            'time_series' => [
                'enabled' => false,
            ],
        ],
        OperationalAnalyticsKpi::DEPARTMENT => [
            'kpi' => OperationalAnalyticsKpi::DEPARTMENT,
            'type' => 'counter',
            'dimensions' => [
                OperationalAnalyticsDimension::SPECIALTY_ID,
                OperationalAnalyticsDimension::SCHOOL_BRANCH_ID
            ],
            'source_events' => [
                OperationalAnalyticsEvent::DEPARTMENT_CREATED,
            ],
            'time_series' => [
                'enabled' => false,
            ],
        ],
        OperationalAnalyticsKpi::COURSE => [
            'kpi' => OperationalAnalyticsKpi::COURSE,
            'type' => 'counter',
            'dimensions' => [
                OperationalAnalyticsDimension::SPECIALTY_ID,
                OperationalAnalyticsDimension::SCHOOL_BRANCH_ID,
                OperationalAnalyticsDimension::LEVEL_ID,
            ],
            'source_events' => [
                OperationalAnalyticsEvent::COURSE_CREATED,
            ],
            'time_series' => [
                'enabled' => false,
            ],
        ],
        OperationalAnalyticsKpi::TEACHER => [
            'kpi' => OperationalAnalyticsKpi::TEACHER,
            'type' => 'counter',
            'dimensions' => [
                OperationalAnalyticsDimension::SCHOOL_BRANCH_ID
            ],
            'source_events' => [
                OperationalAnalyticsEvent::TEACHER_CREATED,
            ],
            'time_series' => [
                'enabled' => false,
            ],
        ],
        OperationalAnalyticsKpi::HALL => [
            'kpi' => OperationalAnalyticsKpi::HALL,
            'type' => 'counter',
            'dimensions' => [
                OperationalAnalyticsDimension::SCHOOL_BRANCH_ID
            ],
            'source_events' => [
                OperationalAnalyticsEvent::HALL_CREATED,
            ],
            'time_series' => [
                'enabled' => false,
            ],
        ],
        OperationalAnalyticsKpi::STUDENT_DROPOUT => [
            'kpi' => OperationalAnalyticsKpi::STUDENT_DROPOUT,
            'type' => 'counter',
            'dimensions' => [
                OperationalAnalyticsDimension::LEVEL_ID,
                OperationalAnalyticsDimension::SPECIALTY_ID,
                OperationalAnalyticsDimension::DEPARTMENT_ID,
                OperationalAnalyticsDimension::SCHOOL_BRANCH_ID
            ],
            'source_events' => [
                OperationalAnalyticsEvent::STUDENT_DROPOUT,
            ],
            'time_series' => [
                'enabled' => true,
                'granularities' => ['year'],
            ],
        ],
        OperationalAnalyticsKpi::TEACHER_DROPOUT => [
            'kpi' => OperationalAnalyticsKpi::TEACHER_DROPOUT,
            'type' => 'counter',
            'dimensions' => [
                OperationalAnalyticsDimension::SCHOOL_BRANCH_ID,
            ],
            'source_events' => [
                OperationalAnalyticsEvent::TEACHER_DROPOUT,
            ],
            'time_series' => [
                'enabled' => true,
                'granularities' => ['year'],
            ],
        ],
        OperationalAnalyticsKpi::STAFF_DROPOUT => [
            'kpi' => OperationalAnalyticsKpi::STAFF_DROPOUT,
            'type' => 'counter',
            'dimensions' => [
                OperationalAnalyticsDimension::SCHOOL_BRANCH_ID,
            ],
            'source_events' => [
                OperationalAnalyticsEvent::STAFF_DROPOUT,
            ],
            'time_series' => [
                'enabled' => true,
                'granularities' => ['year'],
            ],
        ],
        OperationalAnalyticsKpi::TEACHER_COURSE => [
            'kpi' => OperationalAnalyticsKpi::TEACHER,
            'type' => 'counter',
            'dimensions' => [
                OperationalAnalyticsDimension::TEACHER_ID,
                OperationalAnalyticsDimension::LEVEL_ID,
                OperationalAnalyticsDimension::SCHOOL_BRANCH_ID
            ],
            'source_events' => [
                OperationalAnalyticsEvent::TEACHER_COURSE_ASSIGNED,
            ],
            'time_series' => [
                'enabled' => true,
                'granularities' => ['year'],
            ],
        ],
        OperationalAnalyticsKpi::TEACHER_SPECIALTY => [
            'kpi' => OperationalAnalyticsKpi::TEACHER_SPECIALTY,
            'type' => 'counter',
            'dimensions' => [
                OperationalAnalyticsDimension::TEACHER_ID,
                OperationalAnalyticsDimension::SPECIALTY_ID,
            ],
            'source_events' => [
                OperationalAnalyticsEvent::TEACHER_SPECIALTY,
            ],
            'time_series' => [
                'enabled' => true,
                'granularities' => ['year'],
            ],
        ],
    ];
   }
}
