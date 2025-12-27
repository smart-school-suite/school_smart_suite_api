<?php

namespace App\Constant\Analytics\Election;

class ElectionAnalyticsKpi
{
    public const ELECTIONS = "elections_count";
    //candidate
    public const ELECTION_CANDIDATE = "election_candidate_count";
    public const ELECTION_TYPE_CANDIDATE = "election_type_candidate_count";
    public const SPECIALTY_ELECTION_CANDIDATE = "specialty_election_candidate_count";
    public const DEPARTMENT_ELECTION_CANDIDATE = "department_election_candidate_count";
    //vote
    public const CANDIDATE_VOTE_TALLY = "candidate_vote_tally";
    public const CANDIDATE_VOTE_TALLY_SOURCE = "candidate_vote_tally_source";
    public const DEPARTMENT_VOTE_TALLY_SOURCE = "department_vote_tally_source";
    public const SPECIALTY_VOTE_TALLY_SOURCE = "specialty_vote_tally_count";
    public const LEVEL_VOTE_TALLY_SOURCE = "level_vote_tally_count";
    public const ELECTION_ROLE = "election_roles_count";
    public const ELECTION_VOTE = "election_vote_count";
    public const ELECTION_TYPE_VOTE = "election_type_vote_count";
    public const ELECTION_TYPE = "election_type_count";
    //applications
    public const ELECTION_APPLICATION = "election_applications_count";
    public const ELECTION_ROLE_APPLICATION = "election_role_application_count";
    public const ELECTION_TYPE_APPLICATION = "election_type_application_count";
    public const ELECTION_APPLICATION_REJECTION = "election_application_rejections_count";
    public const ELECTION_ROLE_APPLICATION_REJECTION = "election_role_application_rejection";
    public const ELECTION_ROLE_APPLICATION_APPROVAL = "election_role_application_approval";
    public const ELECTION_APPLICATION_APPROVAL = "election_application_approvals_count";
    public const ELECTION_TYPE_APPLICATION_REJECTION = "election_type_application_rejection_count";
    public const ELECTION_TYPE_APPLICATION_APPROVAL = "election_type_application_approval_count";

}
