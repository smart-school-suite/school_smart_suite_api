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
                "kpi" => ElectionAnalyticsKpi::ELECTIONS,
                "name" => "Total Elections Count",
                "description" => "The total number of elections created in the system.",
                "type" => "counter",
                "dimensions" => [
                    ElectionAnalyticsDimension::SCHOOL_BRANCH_ID,
                    ElectionAnalyticsDimension::YEAR
                ],
                "source_events" => [
                    ElectionAnalyticsEvent::ELECTION_CREATED,
                ],
                "time_series" => [
                    "enabled" => true,
                    "granularities" => ["year"],
                ],
            ],

            ElectionAnalyticsKpi::ELECTION_TYPE => [
                "kpi" => ElectionAnalyticsKpi::ELECTION_TYPE,
                "name" => "Election Types Count",
                "description" => "The number of distinct election types (e.g., Student Council, Class Representative, Department Rep) configured in the system.",
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
                "kpi" => ElectionAnalyticsKpi::ELECTION_ROLE,
                "name" => "Election Roles Count",
                "description" => "The total number of election roles/positions (e.g., President, Vice President, Secretary) created across all elections.",
                "type" => "counter",
                "dimensions" => [
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
                "name" => "Total Election Applications Count",
                "description" => "The total number of applications submitted across all elections and roles.",
                "type" => "counter",
                "dimensions" => [
                    ElectionAnalyticsDimension::SCHOOL_BRANCH_ID,
                    ElectionAnalyticsDimension::ELECTION_ID
                ],
                "source_events" => [
                    ElectionAnalyticsEvent::ELECTION_APPLICATION_SUBMITTED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],

            ElectionAnalyticsKpi::ELECTION_ROLE_APPLICATION => [
                "kpi" => ElectionAnalyticsKpi::ELECTION_ROLE_APPLICATION,
                "name" => "Applications per Election Role",
                "description" => "The number of applications submitted for a specific election role within a specific election.",
                "type" => "counter",
                "dimensions" => [
                    ElectionAnalyticsDimension::SCHOOL_BRANCH_ID,
                    ElectionAnalyticsDimension::ELECTION_ROLE_ID,
                    ElectionAnalyticsDimension::ELECTION_ID,
                ],
                "source_events" => [
                    ElectionAnalyticsEvent::ELECTION_APPLICATION_SUBMITTED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],

            ElectionAnalyticsKpi::ELECTION_TYPE_APPLICATION => [
                "kpi" => ElectionAnalyticsKpi::ELECTION_TYPE_APPLICATION,
                "name" => "Applications per Election Type",
                "description" => "The number of applications submitted for roles grouped by election type.",
                "type" => "counter",
                "dimensions" => [
                    ElectionAnalyticsDimension::SCHOOL_BRANCH_ID,
                    ElectionAnalyticsDimension::ELECTION_ROLE_ID,
                    ElectionAnalyticsDimension::ELECTION_TYPE_ID,
                ],
                "source_events" => [
                    ElectionAnalyticsEvent::ELECTION_APPLICATION_SUBMITTED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],

            ElectionAnalyticsKpi::ELECTION_APPLICATION_APPROVAL => [
                "kpi" => ElectionAnalyticsKpi::ELECTION_APPLICATION_APPROVAL,
                "name" => "Approved Applications per Election",
                "description" => "The total number of applications approved (i.e., candidates accepted) for a specific election.",
                "type" => "counter",
                "dimensions" => [
                    ElectionAnalyticsDimension::SCHOOL_BRANCH_ID,
                    ElectionAnalyticsDimension::ELECTION_ID
                ],
                "source_events" => [
                    ElectionAnalyticsEvent::ELECTION_APPLICATION_APPROVED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],

            ElectionAnalyticsKpi::ELECTION_APPLICATION_REJECTION => [
                "kpi" => ElectionAnalyticsKpi::ELECTION_APPLICATION_REJECTION,
                "name" => "Rejected Applications per Election",
                "description" => "The total number of applications rejected for a specific election.",
                "type" => "counter",
                "dimensions" => [
                    ElectionAnalyticsDimension::SCHOOL_BRANCH_ID,
                    ElectionAnalyticsDimension::ELECTION_ID
                ],
                "source_events" => [
                    ElectionAnalyticsEvent::ELECTION_APPLICATION_REJECTED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],

            ElectionAnalyticsKpi::ELECTION_TYPE_APPLICATION_APPROVAL => [
                "kpi" => ElectionAnalyticsKpi::ELECTION_TYPE_APPLICATION_APPROVAL,
                "name" => "Approved Applications per Election Type",
                "description" => "The number of applications that were approved, aggregated by election type.",
                "type" => "counter",
                "dimensions" => [
                    ElectionAnalyticsDimension::SCHOOL_BRANCH_ID,
                    ElectionAnalyticsDimension::ELECTION_TYPE_ID
                ],
                "source_events" => [
                    ElectionAnalyticsEvent::ELECTION_APPLICATION_APPROVED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],

            ElectionAnalyticsKpi::ELECTION_TYPE_APPLICATION_REJECTION => [
                "kpi" => ElectionAnalyticsKpi::ELECTION_TYPE_APPLICATION_REJECTION,
                "name" => "Rejected Applications per Election Type",
                "description" => "The number of applications that were rejected, aggregated by election type.",
                "type" => "counter",
                "dimensions" => [
                    ElectionAnalyticsDimension::SCHOOL_BRANCH_ID,
                    ElectionAnalyticsDimension::ELECTION_TYPE_ID
                ],
                "source_events" => [
                    ElectionAnalyticsEvent::ELECTION_APPLICATION_REJECTED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],

            ElectionAnalyticsKpi::ELECTION_ROLE_APPLICATION_REJECTION => [
                "kpi" => ElectionAnalyticsKpi::ELECTION_ROLE_APPLICATION_REJECTION,
                "type" => "counter",
                "dimensions" => [
                    ElectionAnalyticsDimension::SCHOOL_BRANCH_ID,
                    ElectionAnalyticsDimension::ELECTION_ID,
                    ElectionAnalyticsDimension::ELECTION_TYPE_ID
                ],
                "source_events" => [
                    ElectionAnalyticsEvent::ELECTION_APPLICATION_REJECTED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],

            ElectionAnalyticsKpi::ELECTION_ROLE_APPLICATION_APPROVAL => [
                "kpi" => ElectionAnalyticsKpi::ELECTION_ROLE_APPLICATION_APPROVAL,
                "type" => "counter",
                "dimensions" => [
                    ElectionAnalyticsDimension::SCHOOL_BRANCH_ID,
                    ElectionAnalyticsDimension::ELECTION_ID,
                    ElectionAnalyticsDimension::ELECTION_TYPE_ID
                ],
                "source_events" => [
                    ElectionAnalyticsEvent::ELECTION_APPLICATION_APPROVED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],

            ElectionAnalyticsKpi::ELECTION_CANDIDATE => [
                "kpi" => ElectionAnalyticsKpi::ELECTION_CANDIDATE,
                "name" => "Total Candidates Count",
                "description" => "The total number of approved candidates across all elections (typically derived from approved applications).",
                "type" => "counter",
                "dimensions" => [
                    ElectionAnalyticsDimension::SCHOOL_BRANCH_ID,
                    ElectionAnalyticsDimension::ELECTION_ID
                ],
                "source_events" => [
                    ElectionAnalyticsEvent::ELECTION_APPLICATION_APPROVED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],

            ElectionAnalyticsKpi::ELECTION_TYPE_CANDIDATE => [
                "kpi" => ElectionAnalyticsKpi::ELECTION_TYPE_CANDIDATE,
                "name" => "Candidates per Election Type",
                "description" => "The number of approved candidates grouped by election type.",
                "type" => "counter",
                "dimensions" => [
                    ElectionAnalyticsDimension::SCHOOL_BRANCH_ID,
                    ElectionAnalyticsDimension::ELECTION_TYPE_ID
                ],
                "source_events" => [
                    ElectionAnalyticsEvent::ELECTION_APPLICATION_APPROVED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],

            ElectionAnalyticsKpi::SPECIALTY_ELECTION_CANDIDATE => [
                "kpi" => ElectionAnalyticsKpi::SPECIALTY_ELECTION_CANDIDATE,
                "name" => "Candidates in Specialty Elections",
                "description" => "The number of approved candidates in specialty-specific elections.",
                "type" => "counter",
                "dimensions" => [
                    ElectionAnalyticsDimension::SCHOOL_BRANCH_ID,
                    ElectionAnalyticsDimension::SPECIALTY_ID
                ],
                "source_events" => [
                    ElectionAnalyticsEvent::ELECTION_APPLICATION_APPROVED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],

            ElectionAnalyticsKpi::DEPARTMENT_ELECTION_CANDIDATE => [
                "kpi" => ElectionAnalyticsKpi::DEPARTMENT_ELECTION_CANDIDATE,
                "name" => "Candidates in Department Elections",
                "description" => "The number of approved candidates in department-level elections.",
                "type" => "counter",
                "dimensions" => [
                    ElectionAnalyticsDimension::SCHOOL_BRANCH_ID,
                    ElectionAnalyticsDimension::DEPARTMENT_ID
                ],
                "source_events" => [
                    ElectionAnalyticsEvent::ELECTION_APPLICATION_APPROVED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],

            ElectionAnalyticsKpi::ELECTION_VOTE => [
                "kpi" => ElectionAnalyticsKpi::ELECTION_VOTE,
                "name" => "Total Votes per Election",
                "description" => "The total number of votes cast in a specific election.",
                "type" => "counter",
                "dimensions" => [
                    ElectionAnalyticsDimension::SCHOOL_BRANCH_ID,
                    ElectionAnalyticsDimension::ELECTION_ID
                ],
                "source_events" => [
                    ElectionAnalyticsEvent::VOTE_CASTED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],

            ElectionAnalyticsKpi::ELECTION_TYPE_VOTE => [
                "kpi" => ElectionAnalyticsKpi::ELECTION_TYPE_VOTE,
                "name" => "Total Votes per Election Type",
                "description" => "The total number of votes cast across all elections of the same type.",
                "type" => "counter",
                "dimensions" => [
                    ElectionAnalyticsDimension::SCHOOL_BRANCH_ID,
                    ElectionAnalyticsDimension::ELECTION_TYPE_ID
                ],
                "source_events" => [
                    ElectionAnalyticsEvent::VOTE_CASTED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],

            ElectionAnalyticsKpi::CANDIDATE_VOTE_TALLY => [
                "kpi" => ElectionAnalyticsKpi::CANDIDATE_VOTE_TALLY,
                "name" => "Vote Tally per Candidate",
                "description" => "The number of votes received by each individual candidate.",
                "type" => "counter",
                "dimensions" => [
                    ElectionAnalyticsDimension::SCHOOL_BRANCH_ID,
                    ElectionAnalyticsDimension::ELECTION_ID,
                    ElectionAnalyticsDimension::CANDIDATE_ID
                ],
                "source_events" => [
                    ElectionAnalyticsEvent::VOTE_CASTED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],

            ElectionAnalyticsKpi::CANDIDATE_VOTE_TALLY_SOURCE => [
                "kpi" => ElectionAnalyticsKpi::CANDIDATE_VOTE_TALLY_SOURCE,
                "name" => "Candidate Vote Tally by Source",
                "description" => "The number of votes received by a candidate, broken down by voter source (e.g., department, level, specialty).",
                "type" => "counter",
                "dimensions" => [
                    ElectionAnalyticsDimension::SCHOOL_BRANCH_ID,
                    ElectionAnalyticsDimension::ELECTION_ID,
                    ElectionAnalyticsDimension::CANDIDATE_ID,
                    // ElectionAnalyticsDimension::VOTER_SOURCE // generic source dimension
                ],
                "source_events" => [
                    ElectionAnalyticsEvent::VOTE_CASTED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],

            ElectionAnalyticsKpi::DEPARTMENT_VOTE_TALLY_SOURCE => [
                "kpi" => ElectionAnalyticsKpi::DEPARTMENT_VOTE_TALLY_SOURCE,
                "name" => "Vote Tally by Department Source",
                "description" => "The distribution of votes cast, broken down by the voter's department.",
                "type" => "counter",
                "dimensions" => [
                    ElectionAnalyticsDimension::SCHOOL_BRANCH_ID,
                    ElectionAnalyticsDimension::ELECTION_ID,
                    ElectionAnalyticsDimension::DEPARTMENT_ID
                ],
                "source_events" => [
                    ElectionAnalyticsEvent::VOTE_CASTED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],

            ElectionAnalyticsKpi::SPECIALTY_VOTE_TALLY_SOURCE => [
                "kpi" => ElectionAnalyticsKpi::SPECIALTY_VOTE_TALLY_SOURCE,
                "name" => "Vote Tally by Specialty Source",
                "description" => "The distribution of votes cast, broken down by the voter's specialty.",
                "type" => "counter",
                "dimensions" => [
                    ElectionAnalyticsDimension::SCHOOL_BRANCH_ID,
                    ElectionAnalyticsDimension::ELECTION_ID,
                    ElectionAnalyticsDimension::SPECIALTY_ID
                ],
                "source_events" => [
                    ElectionAnalyticsEvent::VOTE_CASTED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],

            ElectionAnalyticsKpi::LEVEL_VOTE_TALLY_SOURCE => [
                "kpi" => ElectionAnalyticsKpi::LEVEL_VOTE_TALLY_SOURCE,
                "name" => "Vote Tally by Academic Level Source",
                "description" => "The distribution of votes cast, broken down by the voter's academic level (e.g., Year 1, Year 2).",
                "type" => "counter",
                "dimensions" => [
                    ElectionAnalyticsDimension::SCHOOL_BRANCH_ID,
                    ElectionAnalyticsDimension::ELECTION_ID,
                    ElectionAnalyticsDimension::LEVEL_ID
                ],
                "source_events" => [
                    ElectionAnalyticsEvent::VOTE_CASTED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],
        ];
    }
}
