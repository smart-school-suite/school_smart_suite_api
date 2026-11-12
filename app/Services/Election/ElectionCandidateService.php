<?php

namespace App\Services\Election;

use App\Models\Election\Election;
use App\Models\Election\ElectionCandidate;
use App\Services\Election\Support\ElectionParticipantType;
use App\Services\Election\Support\FindsElectionRecords;
use Illuminate\Support\Facades\DB;

class ElectionCandidateService
{
    use FindsElectionRecords;

    private const RELATIONS = ['candidateable', 'position:id,name,seats', 'application', 'election:id,title,status'];

    public function getCandidates(object $currentSchool, array $filters = [])
    {
        return ElectionCandidate::where('school_branch_id', $currentSchool->id)
            ->when($filters['election_id'] ?? null, fn($q, $id) => $q->where('election_id', $id))
            ->when($filters['election_role_id'] ?? null, fn($q, $id) => $q->where('election_role_id', $id))
            ->when($filters['status'] ?? null, fn($q, $status) => $q->where('status', $status))
            ->with(self::RELATIONS)
            ->get()
            ->map(fn($candidate) => $this->present($candidate));
    }

    /**
     * Active candidates of an election grouped by position (ballot view).
     */
    public function getBallot(object $currentSchool, string $electionId): array
    {
        $election = $this->findInBranch(Election::class, $electionId, $currentSchool);

        return ElectionCandidate::where('election_id', $election->id)
            ->where('status', ElectionCandidate::STATUS_ACTIVE)
            ->with(['candidateable', 'position:id,name,seats,description', 'application:id,manifesto,personal_vision,commitment_statement'])
            ->get()
            ->groupBy('election_role_id')
            ->map(fn($candidates) => [
                'position' => $candidates->first()->position,
                'candidates' => $candidates->map(fn($candidate) => $this->present($candidate))->values(),
            ])
            ->values()
            ->all();
    }

    public function getCandidateDetails(object $currentSchool, string $candidateId): array
    {
        $candidate = $this->findInBranch(ElectionCandidate::class, $candidateId, $currentSchool, self::RELATIONS);
        return $this->present($candidate) + ['vote_count' => $candidate->election->is_results_published ? $candidate->votes()->count() : null];
    }

    public function disqualifyCandidates(object $currentSchool, array $candidateIds, ?string $reason = null): array
    {
        return $this->setStatus($currentSchool, $candidateIds, ElectionCandidate::STATUS_DISQUALIFIED, $reason);
    }

    public function reinstateCandidates(object $currentSchool, array $candidateIds): array
    {
        return $this->setStatus($currentSchool, $candidateIds, ElectionCandidate::STATUS_ACTIVE, null);
    }

    private function setStatus(object $currentSchool, array $candidateIds, string $status, ?string $reason): array
    {
        return DB::transaction(fn() => collect($candidateIds)->map(function ($id) use ($currentSchool, $status, $reason) {
            $candidate = $this->findInBranch(ElectionCandidate::class, $id, $currentSchool, ['election', 'application']);

            if (in_array($candidate->election->status, [Election::STATUS_COMPLETED, Election::STATUS_CANCELLED], true)) {
                $this->fail("Election closed", "Candidates cannot be changed after the election has ended.", 409);
            }
            if ($status === ElectionCandidate::STATUS_ACTIVE && $candidate->status === ElectionCandidate::STATUS_WITHDRAWN) {
                $this->fail("Candidate withdrew", "A candidate who withdrew cannot be reinstated.", 409);
            }

            $candidate->update(['status' => $status, 'status_reason' => $reason]);
            return $this->present($candidate->load(self::RELATIONS));
        })->all());
    }

    public function present(ElectionCandidate $candidate): array
    {
        return [
            'id' => $candidate->id,
            'election_id' => $candidate->election_id,
            'position' => $candidate->position,
            'candidate_type' => ElectionParticipantType::keyFor($candidate->candidateable_type),
            'candidate_id' => $candidate->candidateable_id,
            'name' => ElectionParticipantType::displayName($candidate->candidateable),
            'candidate' => $candidate->candidateable,
            'status' => $candidate->status,
            'status_reason' => $candidate->status_reason,
            'application_id' => $candidate->application_id,
            'manifesto' => $candidate->application?->manifesto,
            'personal_vision' => $candidate->application?->personal_vision,
            'commitment_statement' => $candidate->application?->commitment_statement,
        ];
    }
}
