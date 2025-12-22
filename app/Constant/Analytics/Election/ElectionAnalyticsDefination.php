<?php

namespace App\Constant\Analytics\Election;

use App\Constant\Analytics\Election\ElectionAnalyticsEvent;
use App\Constant\Analytics\Election\ElectionAnalyticsKpi;

class ElectionAnalyticsDefination
{
    public static function definations(): array
    {
        return [
            ElectionAnalyticsKpi::ELECTIONS => [
                "kpi" =>  ElectionAnalyticsKpi::ELECTIONS,
                'type' => 'counter',
                'dimensions' => [
                    ElectionAnalyticsDimension::SCHOOL_BRANCH_ID,
                    ElectionAnalyticsDimension::ELECTION_TYPE_ID
                ],
                'source_events' => [
                    ElectionAnalyticsEvent::ELECTION_CREATED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],
            ElectionAnalyticsKpi::ELECTION_TYPE => [
                "kpi" => ElectionAnalyticsKpi::ELECTION_TYPE,
                "type" => "counter",
                "dimensions" => [
                    ElectionAnalyticsDimension::SCHOOL_BRANCH_ID,
                ],
                "source_events" => [
                    ElectionAnalyticsEvent::ELECTION_ROLE_CREATED
                ],
                "time_series" => [
                     "enabled" => false
                ]
            ],
            ElectionAnalyticsKpi::ELECTION_ROLE => [
                "kpi" =>  ElectionAnalyticsKpi::ELECTION_ROLE,
                "type" => "counter",
                "dimensions" => [
                    ElectionAnalyticsDimension::ELECTION_TYPE_ID,
                    ElectionAnalyticsDimension::SCHOOL_BRANCH_ID
                ],
                "source_events" => [
                    ElectionAnalyticsEvent::ELECTION_ROLE_CREATED
                ],
                "time_series" => [
                     "enabled" => false
                ]
            ],
            ElectionAnalyticsKpi::ELECTION_APPLICATION => [
                "kpi" => ElectionAnalyticsKpi::ELECTION_APPLICATION,
                "type" => "counter",
                "dimensions" => [
                    ElectionAnalyticsDimension::SPECIALTY_ID,
                    ElectionAnalyticsDimension::DEPARTMENT_ID,
                    ElectionAnalyticsDimension::GENDER_ID,
                    ElectionAnalyticsDimension::ELECTION_ID,
                    ElectionAnalyticsDimension::ELECTION_TYPE_ID,
                    ElectionAnalyticsDimension::LEVEL_ID,
                    ElectionAnalyticsDimension::SCHOOL_BRANCH_ID,
                    ElectionAnalyticsDimension::ELECTION_ROLE_ID
                ],
                "source_events" => [
                    ElectionAnalyticsEvent::ELECTION_APPLICATION_SUBMITTED
                ],
                "time_series" => [
                     "enabled" => true,
                     "granularities" => ["year"]
                ]
            ],
            ElectionAnalyticsKpi::ELECTION_APPLICATION_APPROVAL => [
                "kpi" => ElectionAnalyticsKpi::ELECTION_APPLICATION_APPROVAL,
                "type" => "counter",
                "dimensions" => [
                    ElectionAnalyticsDimension::SPECIALTY_ID,
                    ElectionAnalyticsDimension::DEPARTMENT_ID,
                    ElectionAnalyticsDimension::GENDER_ID,
                    ElectionAnalyticsDimension::ELECTION_ID,
                    ElectionAnalyticsDimension::ELECTION_TYPE_ID,
                    ElectionAnalyticsDimension::LEVEL_ID,
                    ElectionAnalyticsDimension::SCHOOL_BRANCH_ID,
                    ElectionAnalyticsDimension::ELECTION_ROLE_ID
                ],
                "source_events" => [
                    ElectionAnalyticsEvent::ELECTION_APPLICATION_APPROVED
                ],
                "time_series" => [
                     "enabled" => true,
                     "granularities" => ["year"]
                ]
            ],
            ElectionAnalyticsKpi::ELECTION_APPLICATION_REJECTION => [
                "kpi" => ElectionAnalyticsKpi::ELECTION_APPLICATION_REJECTION,
                "type" => "counter",
                "dimensions" => [
                    ElectionAnalyticsDimension::SPECIALTY_ID,
                    ElectionAnalyticsDimension::DEPARTMENT_ID,
                    ElectionAnalyticsDimension::GENDER_ID,
                    ElectionAnalyticsDimension::ELECTION_ID,
                    ElectionAnalyticsDimension::ELECTION_TYPE_ID,
                    ElectionAnalyticsDimension::LEVEL_ID,
                    ElectionAnalyticsDimension::SCHOOL_BRANCH_ID,
                    ElectionAnalyticsDimension::ELECTION_ROLE_ID
                ],
                "source_events" => [
                    ElectionAnalyticsEvent::ELECTION_APPLICATION_REJECTED
                ],
                "time_series" => [
                     "enabled" => true,
                     "granularities" => ["year"]
                ]
            ],
            ElectionAnalyticsKpi::CANDIDATE_VOTE_TALLY => [
                "kpi" => ElectionAnalyticsKpi::CANDIDATE_VOTE_TALLY,
                "type" => "counter",
                "dimensions" => [
                    ElectionAnalyticsDimension::ELECTION_ID,
                    ElectionAnalyticsDimension::ELECTION_TYPE_ID,
                    ElectionAnalyticsDimension::SCHOOL_BRANCH_ID,
                    ElectionAnalyticsDimension::ELECTION_ROLE_ID,
                    ElectionAnalyticsDimension::CANDIDATE_ID
                ],
                "source_events" => [
                     ElectionAnalyticsEvent::VOTE_CASTED
                ],
                "timeseries" => [
                     "enabled" => false
                ]
            ],
            ElectionAnalyticsKpi::CANDIDATE_VOTE_TALLY_SOURCE => [
                "kpi" => ElectionAnalyticsKpi::CANDIDATE_VOTE_TALLY_SOURCE,
                "type" => "counter",
                "dimensions" => [
                    ElectionAnalyticsDimension::CANDIDATE_ID,
                    ElectionAnalyticsDimension::DEPARTMENT_ID,
                    ElectionAnalyticsDimension::CANDIDATE_ID,
                    ElectionAnalyticsDimension::SCHOOL_BRANCH_ID,
                    ElectionAnalyticsDimension::SPECIALTY_ID,
                    ElectionAnalyticsDimension::LEVEL_ID
                ],
                "source_events" => [
                     ElectionAnalyticsEvent::VOTE_CASTED
                ],
                "timeseries" => [
                     "enabled" => false
                ]
            ]
        ];
    }
}
