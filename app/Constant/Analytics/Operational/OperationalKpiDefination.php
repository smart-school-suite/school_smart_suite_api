<?php

namespace App\Constant\Analytics\Operational;

class OperationalKpiDefination
{
    public static function definitions(): array
    {
        return [
            OperationalAnalyticsKpi::SPECIALTY => [
                'kpi' => OperationalAnalyticsKpi::SPECIALTY,
                'type' => 'counter',
                'dimensions' => [
                    OperationalAnalyticsDimension::SCHOOL_BRANCH_ID
                ],
                'source_events' => [
                    OperationalAnalyticsEvent::SPECIALTY_CREATED,
                ],
                'time_series' => [
                    'enabled' => false,
                ],
            ],
            OperationalAnalyticsKpi::ACTIVE_SPECIALTY => [
                "kpi" => OperationalAnalyticsKpi::ACTIVE_SPECIALTY,
                "type" => "counter",
                "dimensions" => [
                    OperationalAnalyticsDimension::SCHOOL_BRANCH_ID
                ],
                "source_events" => [
                    OperationalAnalyticsEvent::SPECIALTY_CREATED,
                    OperationalAnalyticsEvent::SPECIALTY_ACTIVATED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],
            OperationalAnalyticsKpi::INACTIVE_SPECIALTY => [
                "kpi" => OperationalAnalyticsKpi::INACTIVE_SPECIALTY,
                "type" => "counter",
                "dimensions" => [
                    OperationalAnalyticsDimension::SCHOOL_BRANCH_ID
                ],
                "source_events" => [
                    OperationalAnalyticsEvent::SPECIALTY_DEACTIVATED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],
            OperationalAnalyticsKpi::DEPARTMENT => [
                'kpi' => OperationalAnalyticsKpi::DEPARTMENT,
                'type' => 'counter',
                'dimensions' => [
                    OperationalAnalyticsDimension::SCHOOL_BRANCH_ID
                ],
                'source_events' => [
                    OperationalAnalyticsEvent::DEPARTMENT_CREATED,
                ],
                'time_series' => [
                    'enabled' => false,
                ],
            ],
            OperationalAnalyticsKpi::ACTIVE_DEPARTMENT => [
                "kpi" => OperationalAnalyticsKpi::ACTIVE_DEPARTMENT,
                "type" => "counter",
                "dimensions" => [
                    OperationalAnalyticsDimension::SCHOOL_BRANCH_ID,
                ],
                "source_events" => [
                    OperationalAnalyticsEvent::DEPARTMENT_ACTIVATED,
                    OperationalAnalyticsEvent::DEPARTMENT_CREATED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],
            OperationalAnalyticsKpi::INACTIVE_DEPARTMENT => [
                "kpi" => OperationalAnalyticsKpi::INACTIVE_DEPARTMENT,
                "type" => "counter",
                "dimensions" => [
                    OperationalAnalyticsDimension::SCHOOL_BRANCH_ID
                ],
                "source_events" => [
                    OperationalAnalyticsEvent::DEPARTMENT_DEACTIVATED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],
            OperationalAnalyticsKpi::COURSE => [
                'kpi' => OperationalAnalyticsKpi::COURSE,
                'type' => 'counter',
                'dimensions' => [
                    OperationalAnalyticsDimension::SCHOOL_BRANCH_ID,
                ],
                'source_events' => [
                    OperationalAnalyticsEvent::COURSE_CREATED,
                ],
                'time_series' => [
                    'enabled' => false,
                ],
            ],
            OperationalAnalyticsKpi::SPECIALTY_COURSE => [
                "kpi" => OperationalAnalyticsKpi::SPECIALTY_COURSE,
                "type" => "counter",
                "dimensions" => [
                    OperationalAnalyticsDimension::SCHOOL_BRANCH_ID,
                    OperationalAnalyticsDimension::SPECIALTY_ID
                ],
                "source_events" => [
                    OperationalAnalyticsEvent::COURSE_CREATED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],
            OperationalAnalyticsKpi::DEPARTMENT_COURSE => [
                "kpi" => OperationalAnalyticsKpi::DEPARTMENT_COURSE,
                "type" => "counter",
                "dimensions" => [
                    OperationalAnalyticsDimension::SCHOOL_BRANCH_ID,
                    OperationalAnalyticsDimension::DEPARTMENT_ID
                ],
                "source_events" => [
                    OperationalAnalyticsEvent::COURSE_CREATED
                ],
                "time_series" => [
                    "enabled" => false
                ]
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
            OperationalAnalyticsKpi::TEACHER_SPECIALTY_COUNT => [
                "kpi" => OperationalAnalyticsKpi::TEACHER_SPECIALTY_COUNT,
                "type" => "counter",
                "dimensions" => [
                    OperationalAnalyticsDimension::SCHOOL_BRANCH_ID,
                    OperationalAnalyticsDimension::SPECIALTY_ID
                ],
                "source_events" => [
                    OperationalAnalyticsEvent::TEACHER_SPECIALTY_ASSIGNED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],
            OperationalAnalyticsKpi::TEACHER_DEPARTMENT_COUNT => [
                "kpi" => OperationalAnalyticsKpi::TEACHER_DEPARTMENT_COUNT,
                "type" => "counter",
                "dimensions" => [
                    OperationalAnalyticsDimension::DEPARTMENT_ID,
                    OperationalAnalyticsDimension::SCHOOL_BRANCH_ID
                ],
                "source_events" => [
                    OperationalAnalyticsEvent::TEACHER_SPECIALTY_ASSIGNED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],
            OperationalAnalyticsKpi::STUDENT_DROPOUT => [
                "kpi" => OperationalAnalyticsKpi::STUDENT_DROPOUT,
                "type" => "counter",
                "dimensions" => [
                    OperationalAnalyticsDimension::SCHOOL_BRANCH_ID,
                    OperationalAnalyticsDimension::YEAR,
                    OperationalAnalyticsDimension::GENDER_ID,
                    OperationalAnalyticsDimension::DEPARTMENT_ID,
                    OperationalAnalyticsDimension::SPECIALTY_ID,
                    OperationalAnalyticsDimension::LEVEL_ID
                ],
                "source_events" => [
                    OperationalAnalyticsEvent::STUDENT_DROPOUT
                ],
                "time_series" => [
                    "enabled" => true,
                    "granularities" => ["year"]
                ]
            ],
            OperationalAnalyticsKpi::STUDENT_GENDER_DROPOUT => [
                "kpi" => OperationalAnalyticsKpi::STUDENT_GENDER_DROPOUT,
                "type" => "counter",
                "dimensions" => [
                    OperationalAnalyticsDimension::SCHOOL_BRANCH_ID,
                    OperationalAnalyticsDimension::YEAR,
                    OperationalAnalyticsDimension::GENDER_ID
                ],
                "source_events" => [
                    OperationalAnalyticsEvent::STUDENT_DROPOUT
                ],
                "time_series" => [
                    "enabled" => true,
                    "granularities" => ["year"]
                ]
            ],
            OperationalAnalyticsKpi::STUDENT_DEPARTMENT_DROPOUT => [
                "kpi" => OperationalAnalyticsKpi::STUDENT_DEPARTMENT_DROPOUT,
                "type" => "counter",
                "dimensions" => [
                    OperationalAnalyticsDimension::SCHOOL_BRANCH_ID,
                    OperationalAnalyticsDimension::DEPARTMENT_ID,
                    OperationalAnalyticsDimension::YEAR,
                ],
                "source_events" => [
                    OperationalAnalyticsEvent::STUDENT_DROPOUT
                ],
                "time_series" => [
                    "enabled" => true,
                    "granularities" => ["year"]
                ]
            ],
            OperationalAnalyticsKpi::STUDENT_LEVEL_DPOPOUT => [
                "kpi" => OperationalAnalyticsKpi::STUDENT_LEVEL_DPOPOUT,
                "type" => "counter",
                "dimensions" => [
                    OperationalAnalyticsDimension::SCHOOL_BRANCH_ID,
                    OperationalAnalyticsDimension::LEVEL_ID,
                    OperationalAnalyticsDimension::YEAR
                ],
                "source_events" => [
                    OperationalAnalyticsEvent::STUDENT_DROPOUT
                ],
                "time_series" => [
                    "enabled" => true,
                    "granularities" => ["year"]
                ]
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
            OperationalAnalyticsKpi::ACTIVE_HALL => [
                "kpi" => OperationalAnalyticsKpi::ACTIVE_HALL,
                "type" => "counter",
                "dimensions" => [
                    OperationalAnalyticsDimension::SCHOOL_BRANCH_ID,
                ],
                "source_events" => [
                    OperationalAnalyticsEvent::HALL_ACTIVATED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],
            OperationalAnalyticsKpi::INACTIVE_HALL => [
                "kpi" => OperationalAnalyticsKpi::INACTIVE_HALL,
                "type" => "counter",
                "dimensions" => [
                    OperationalAnalyticsDimension::SCHOOL_BRANCH_ID,
                ],
                "source_events" => [
                    OperationalAnalyticsEvent::HALL_DEACTIVATED
                ],
                "time_series" => [
                    "enabled" => false
                ]
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
            OperationalAnalyticsKpi::TEACHER_GENDER => [
                "kpi" => OperationalAnalyticsKpi::TEACHER_GENDER,
                "type" => "counter",
                "dimensions" => [
                    OperationalAnalyticsDimension::SCHOOL_BRANCH_ID,
                ],
                "source_events" => [
                    OperationalAnalyticsEvent::TEACHER_CREATED,
                ],
                "time_series" => [
                    "enabled" => false
                ]
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
                'kpi' => OperationalAnalyticsKpi::TEACHER_COURSE,
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

        ];
    }
}
