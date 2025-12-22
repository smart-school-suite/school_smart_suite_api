<?php

namespace App\Constant\Analytics\Election;

class ElectionAnalyticsEvent
{
    public const ELECTION_CREATED = "election_created";
    public const ELECTION_UPDATED = "election_updated";
    public const ELECTION_DELETED = "election_deleted";

    public const ELECTION_TYPE_CREATED = "electionRole.created";
    public const ELECTION_TYPE_DELETED =  "electionRole.deleted";

    public const ELECTION_ROLE_CREATED = "electionRole.created";
    public const ELECTION_ROLE_DELETED = "electionRole.deleted";

    public const CANDIDATE_REGISTERED = "candidate_registered";
    public const CANDIDATE_UPDATED = "candidate_updated";
    public const CANDIDATE_DELETED = "candidate_deleted";
    public const CANDIDATE_ELECTED = "candidate_elected";
    public const CANDIDATE_DISQUALIFIED = "candidate_disqualified";

    public const VOTE_CASTED = "vote_casted";

    public const ELECTION_APPLICATION_SUBMITTED = "election_application_submitted";
    public const ELECTION_APPLICATION_APPROVED = "election_application_approved";
    public const ELECTION_APPLICATION_REJECTED = "election_application_rejected";
}
