<?php

namespace App\Services\Election;

use App\Models\Election\Election;
use App\Models\Election\ElectionCandidate;
use App\Models\Election\ElectionOfficeHolder;
use App\Models\Election\ElectionRole;
use App\Models\Election\ElectionVote;
use App\Models\Schooladmin;
use App\Services\Election\Support\ElectionParticipantType;
use App\Services\Election\Support\FindsElectionRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Results are always derived from election_votes; there is no results table.
 */
class ElectionResultService
{
    use FindsElectionRecords;

    public function __construct(
        private ElectionService $electionService,
        private ElectionOfficeHolderService $officeHolderService,
    ) {}

    /**
     * Results for an election. Administrators may follow live results once
     * voting started; other participants only see published results.
     */
    public function getResults(string $electionId, object $currentSchool, ?Model $viewer = null): array
    {
        $election = $this->findInBranch(Election::class, $electionId, $currentSchool, ['electionType', 'systemAcademicYear']);
        $this->electionService->sync($election);

        $isAdmin = $viewer instanceof Schooladmin;
        $votingStarted = in_array($election->status, [Election::STATUS_VOTING_OPEN, Election::STATUS_VOTING_CLOSED, Election::STATUS_COMPLETED], true);

        if (!$election->is_results_published && !($isAdmin && $votingStarted)) {
            $this->fail("Results not available", "The results of this election have not been published yet.", 403);
        }

        $results = $this->tally($election);

        if ($viewer && !$isAdmin) {
            $choices = ElectionVote::where('election_id', $election->id)
                ->where('votable_type', $viewer->getMorphClass())
                ->where('votable_id', $viewer->getKey())
                ->get(['position_id', 'candidate_id']);
            foreach ($results as &$position) {
                $mine = $choices->where('position_id', $position['position_id'])->pluck('candidate_id');
                $position['has_voted'] = $mine->isNotEmpty();
                foreach ($position['candidates'] as &$candidate) {
                    $candidate['voted_by_me'] = $mine->contains($candidate['candidate_id']);
                }
            }
            unset($position, $candidate);
        }

        return [
            'election' => $election,
            'is_final' => (bool) $election->is_results_published,
            'turnout' => $this->electionService->turnout($election),
            'results' => $results,
        ];
    }

    /**
     * Vote counts per candidate grouped by position, with winners resolved by seats.
     */
    public function tally(Election $election): array
    {
        $counts = ElectionVote::where('election_id', $election->id)
            ->select('candidate_id', DB::raw('COUNT(*) as votes'))
            ->groupBy('candidate_id')
            ->pluck('votes', 'candidate_id');

        $holders = $election->is_results_published
            ? ElectionOfficeHolder::where('election_id', $election->id)->pluck('election_candidate_id')->flip()
            : collect();

        $candidates = ElectionCandidate::where('election_id', $election->id)
            ->with('candidateable')
            ->get()
            ->groupBy('election_role_id');

        $positions = ElectionRole::where('school_branch_id', $election->school_branch_id)
            ->where('election_type_id', $election->election_type_id)
            ->whereIn('id', $candidates->keys())
            ->orderBy('name')
            ->get();

        $totalVoters = ElectionVote::where('election_id', $election->id)
            ->select('position_id', DB::raw("COUNT(DISTINCT CONCAT(votable_type, '|', votable_id)) as voters"))
            ->groupBy('position_id')
            ->pluck('voters', 'position_id');

        return $positions->map(function (ElectionRole $position) use ($candidates, $counts, $holders, $totalVoters) {
            $rows = $candidates->get($position->id, collect())
                ->map(fn(ElectionCandidate $candidate) => [
                    'candidate_id' => $candidate->id,
                    'candidate_type' => ElectionParticipantType::keyFor($candidate->candidateable_type),
                    'participant_id' => $candidate->candidateable_id,
                    'name' => ElectionParticipantType::displayName($candidate->candidateable),
                    'participant' => $candidate->candidateable,
                    'status' => $candidate->status,
                    'vote_count' => (int) ($counts[$candidate->id] ?? 0),
                ])
                ->sortByDesc('vote_count')
                ->values();

            $outcome = $this->resolveWinners($rows->where('status', ElectionCandidate::STATUS_ACTIVE)->values(), max(1, $position->seats));
            $positionVoters = (int) ($totalVoters[$position->id] ?? 0);

            return [
                'position_id' => $position->id,
                'position_name' => $position->name,
                'seats' => $position->seats,
                'total_votes' => $rows->sum('vote_count'),
                'total_voters' => $positionVoters,
                'requires_tie_resolution' => $outcome['tied'] !== [],
                'tied_candidate_ids' => $outcome['tied'],
                'candidates' => $rows->map(function ($row) use ($outcome, $holders, $positionVoters) {
                    $row['vote_percentage'] = $positionVoters ? round($row['vote_count'] / $positionVoters * 100, 2) : 0;
                    $row['is_winner'] = $holders->isNotEmpty()
                        ? $holders->has($row['candidate_id'])
                        : in_array($row['candidate_id'], $outcome['winners'], true);
                    $row['is_tied'] = in_array($row['candidate_id'], $outcome['tied'], true);
                    return $row;
                })->all(),
            ];
        })->all();
    }

    /**
     * @return array{winners: array<int, string>, tied: array<int, string>}
     */
    private function resolveWinners($ranked, int $seats): array
    {
        if ($ranked->count() <= $seats) {
            return ['winners' => $ranked->pluck('candidate_id')->all(), 'tied' => []];
        }

        $cutoff = $ranked[$seats - 1]['vote_count'];
        $above = $ranked->filter(fn($r) => $r['vote_count'] > $cutoff)->pluck('candidate_id');
        $atCutoff = $ranked->filter(fn($r) => $r['vote_count'] === $cutoff)->pluck('candidate_id');

        if ($above->count() + $atCutoff->count() === $seats) {
            return ['winners' => $above->merge($atCutoff)->all(), 'tied' => []];
        }

        // The remaining seats cannot be filled without breaking a tie.
        return ['winners' => $above->all(), 'tied' => $atCutoff->values()->all()];
    }

    /**
     * Finalise the election: confirm winners, create their office tenure and
     * mark the election completed. Ties must be resolved explicitly with
     * $tieResolutions = [position_id => [candidate_id, ...]].
     */
    public function publishResults(string $electionId, object $currentSchool, array $tieResolutions = []): array
    {
        $result = DB::transaction(function () use ($electionId, $currentSchool, $tieResolutions) {
            $election = $this->findInBranch(Election::class, $electionId, $currentSchool, ['systemAcademicYear'], true);
            $this->electionService->sync($election);

            if ($election->is_results_published) {
                $this->fail("Results already published", "The results of this election have already been published.", 409);
            }
            if ($election->status !== Election::STATUS_VOTING_CLOSED) {
                $this->fail("Voting not closed", "Results can only be published after voting has closed.", 409);
            }

            $tally = $this->tally($election);
            $positions = ElectionRole::whereIn('id', collect($tally)->pluck('position_id'))->with('officeTerm')->get()->keyBy('id');

            foreach ($tally as $position) {
                $winners = collect($position['candidates'])->where('is_winner', true)->pluck('candidate_id');

                if ($position['requires_tie_resolution']) {
                    $chosen = collect($tieResolutions[$position['position_id']] ?? []);
                    $open = $position['seats'] - $winners->count();
                    if ($chosen->count() !== $open || $chosen->diff($position['tied_candidate_ids'])->isNotEmpty()) {
                        $this->fail(
                            "Tie must be resolved",
                            "Position {$position['position_name']} has a tie. Choose {$open} winner(s) among the tied candidates.",
                            422
                        );
                    }
                    $winners = $winners->merge($chosen);
                }

                foreach ($winners as $candidateId) {
                    $candidate = ElectionCandidate::with('candidateable')->find($candidateId);
                    $voteCount = collect($position['candidates'])->firstWhere('candidate_id', $candidateId)['vote_count'];
                    $this->officeHolderService->createElectedHolder($election, $positions[$position['position_id']], $candidate, $voteCount);
                }
            }

            $election->update([
                'status' => Election::STATUS_COMPLETED,
                'is_results_published' => true,
                'results_published_at' => Carbon::now(),
            ]);
            $this->electionService->notifyTransition($election, Election::STATUS_COMPLETED);

            return $election;
        });

        return $this->getResults($result->id, $currentSchool);
    }

    /**
     * Completed elections of a branch with their winners (election history).
     */
    public function getElectionHistory(object $currentSchool, ?string $electionTypeId = null)
    {
        return Election::where('school_branch_id', $currentSchool->id)
            ->where('status', Election::STATUS_COMPLETED)
            ->when($electionTypeId, fn($q) => $q->where('election_type_id', $electionTypeId))
            ->with([
                'electionType:id,election_title',
                'systemAcademicYear:id,name',
                'officeHolders' => fn($q) => $q->where('appointment_type', ElectionOfficeHolder::APPOINTMENT_ELECTED),
                'officeHolders.holderable',
                'officeHolders.position:id,name',
            ])
            ->withCount(['candidates', 'votes'])
            ->orderByDesc('voting_end')
            ->get()
            ->map(fn(Election $election) => [
                'election' => $election->only(['id', 'title', 'status', 'voting_start', 'voting_end', 'results_published_at']),
                'election_type' => $election->electionType,
                'system_academic_year' => $election->systemAcademicYear,
                'candidates_count' => $election->candidates_count,
                'votes_count' => $election->votes_count,
                'winners' => $election->officeHolders->map(fn($holder) => [
                    'office_holder_id' => $holder->id,
                    'position' => $holder->position,
                    'holder_type' => ElectionParticipantType::keyFor($holder->holderable_type),
                    'holder_id' => $holder->holderable_id,
                    'name' => ElectionParticipantType::displayName($holder->holderable),
                    'vote_count' => $holder->vote_count,
                    'status' => $holder->status,
                ])->values(),
            ]);
    }
}
