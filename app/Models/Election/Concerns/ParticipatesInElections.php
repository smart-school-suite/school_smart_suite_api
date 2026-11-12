<?php

namespace App\Models\Election\Concerns;

use App\Models\Election\ElectionApplication;
use App\Models\Election\ElectionCandidate;
use App\Models\Election\ElectionOfficeHolder;
use App\Models\Election\ElectionParticipant;
use App\Models\Election\ElectionVote;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Election relationships shared by every participant type
 * (Student, Teacher, Schooladmin).
 */
trait ParticipatesInElections
{
    public function electionParticipations(): MorphMany
    {
        return $this->morphMany(ElectionParticipant::class, 'participantable');
    }

    public function electionApplications(): MorphMany
    {
        return $this->morphMany(ElectionApplication::class, 'applicantable');
    }

    public function electionCandidacies(): MorphMany
    {
        return $this->morphMany(ElectionCandidate::class, 'candidateable');
    }

    public function electionVotes(): MorphMany
    {
        return $this->morphMany(ElectionVote::class, 'votable');
    }

    public function electionOfficeHolders(): MorphMany
    {
        return $this->morphMany(ElectionOfficeHolder::class, 'holderable');
    }
}
