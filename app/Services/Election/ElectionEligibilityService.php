<?php

namespace App\Services\Election;

use App\Models\Election\Election;
use App\Models\Election\ElectionOfficeHolder;
use App\Models\Election\ElectionParticipant;
use App\Models\Election\ElectionRole;
use App\Services\Election\Support\OfficeTenureResolver;
use Illuminate\Database\Eloquent\Model;

/**
 * Central eligibility rules for voting and candidacy.
 */
class ElectionEligibilityService
{
    public function __construct(private OfficeTenureResolver $tenureResolver) {}

    public function participantRecord(Election $election, Model $participant, bool $lock = false): ?ElectionParticipant
    {
        return ElectionParticipant::where('election_id', $election->id)
            ->where('participantable_type', $participant->getMorphClass())
            ->where('participantable_id', $participant->getKey())
            ->when($lock, fn($q) => $q->lockForUpdate())
            ->first();
    }

    public function canVote(Election $election, Model $participant): bool
    {
        return (bool) $this->participantRecord($election, $participant)?->can_vote;
    }

    public function canApply(Election $election, Model $participant): bool
    {
        return (bool) $this->participantRecord($election, $participant)?->can_apply;
    }

    /**
     * Reasons (empty when eligible) why the participant may not stand for a
     * position, based on the position's office-term rules.
     *
     * @return array<int, string>
     */
    public function officeTermViolations(Election $election, ElectionRole $position, Model $participant): array
    {
        $term = $this->tenureResolver->termFor($position);
        // Every tenure counts (scheduled, active, completed or terminated),
        // except one created by this very election.
        $previousTerms = ElectionOfficeHolder::where('school_branch_id', $position->school_branch_id)
            ->where('election_role_id', $position->id)
            ->where('holderable_type', $participant->getMorphClass())
            ->where('holderable_id', $participant->getKey())
            ->where(fn($q) => $q->whereNull('election_id')->orWhere('election_id', '!=', $election->id))
            ->get(['id', 'status', 'election_id']);

        $violations = [];
        $maximum = $term->effectiveMaximumTerms();

        if ($previousTerms->isNotEmpty() && !$term->allow_reelection) {
            $violations[] = "Re-election is not permitted for the position {$position->name}.";
        } elseif ($maximum !== null && $previousTerms->count() >= $maximum) {
            $violations[] = "The maximum of {$maximum} term(s) for the position {$position->name} has been reached.";
        }

        return $violations;
    }
}
