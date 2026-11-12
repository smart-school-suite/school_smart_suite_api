<?php

namespace App\Services\Election;

use App\Models\Election\Election;
use App\Models\Election\ElectionParticipant;
use App\Services\Election\Support\ElectionAudienceResolver;
use App\Services\Election\Support\ElectionParticipantType;
use App\Services\Election\Support\FindsElectionRecords;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Configures who can vote and who can apply for candidacy.
 *
 * The configuration is kept on elections.participants (JSON, like
 * announcements.audience) and materialised into election_participants so that
 * eligibility checks are a single indexed lookup.
 */
class ElectionParticipantService
{
    use FindsElectionRecords;

    public function __construct(private ElectionAudienceResolver $audienceResolver) {}

    public function configureParticipants(object $currentSchool, string $electionId, array $config): array
    {
        $election = $this->findInBranch(Election::class, $electionId, $currentSchool);
        $this->assertConfigurable($election);

        $normalised = $this->audienceResolver->normalise($config);

        if (!$this->audienceResolver->isGroupConfigured($normalised, ElectionAudienceResolver::GROUP_VOTERS)) {
            $this->fail("Voters not configured", "Select at least one group of eligible voters.");
        }
        if (!$this->audienceResolver->isGroupConfigured($normalised, ElectionAudienceResolver::GROUP_CANDIDATES)) {
            $this->fail("Candidates not configured", "Select at least one group of participants who may apply for candidacy.");
        }

        $resolved = $this->audienceResolver->resolve($currentSchool, $normalised);

        if (!empty($resolved['missing'])) {
            $this->fail(
                "Some participants not found",
                "One or more selected participants were not found in this school branch: "
                    . collect($resolved['missing'])->map(fn($ids, $type) => count((array) $ids) . " {$type}")->implode(', ') . ".",
                404
            );
        }

        DB::transaction(function () use ($election, $normalised, $resolved) {
            $election->update(['participants' => $normalised]);
            $this->syncRows($election, $resolved['rows']);
        });

        return $this->summary($election->fresh());
    }

    /**
     * Re-resolve the stored configuration (e.g. after new students enrolled).
     */
    public function refreshParticipants(object $currentSchool, string $electionId): array
    {
        $election = $this->findInBranch(Election::class, $electionId, $currentSchool);
        $this->assertConfigurable($election);

        if (empty($election->participants)) {
            $this->fail("Participants not configured", "Configure the election participants before refreshing them.");
        }

        $resolved = $this->audienceResolver->resolve($currentSchool, $election->participants);
        DB::transaction(fn() => $this->syncRows($election, $resolved['rows']));

        return $this->summary($election->fresh());
    }

    public function copyParticipantsFromElection(object $currentSchool, string $electionId, string $sourceElectionId): array
    {
        $source = $this->findInBranch(Election::class, $sourceElectionId, $currentSchool);

        if (empty($source->participants)) {
            $this->fail("Source election has no participants", "The selected election has no participant configuration to copy.");
        }

        return $this->configureParticipants($currentSchool, $electionId, $source->participants);
    }

    public function getParticipants(object $currentSchool, string $electionId, array $filters = [])
    {
        $election = $this->findInBranch(Election::class, $electionId, $currentSchool);

        $query = ElectionParticipant::where('school_branch_id', $currentSchool->id)
            ->where('election_id', $election->id)
            ->with('participantable');

        if (!empty($filters['participant_type'])) {
            $query->where('participantable_type', ElectionParticipantType::classFor($filters['participant_type']));
        }
        if (isset($filters['can_vote'])) {
            $query->where('can_vote', filter_var($filters['can_vote'], FILTER_VALIDATE_BOOLEAN));
        }
        if (isset($filters['can_apply'])) {
            $query->where('can_apply', filter_var($filters['can_apply'], FILTER_VALIDATE_BOOLEAN));
        }

        return [
            'configuration' => $election->participants,
            'summary' => $this->summary($election)['summary'],
            'participants' => $query->paginate($filters['per_page'] ?? 50)->through(fn($row) => [
                'id' => $row->id,
                'participant_type' => ElectionParticipantType::keyFor($row->participantable_type),
                'participant_id' => $row->participantable_id,
                'name' => ElectionParticipantType::displayName($row->participantable),
                'can_vote' => $row->can_vote,
                'can_apply' => $row->can_apply,
            ]),
        ];
    }

    public function summary(Election $election): array
    {
        $counts = ElectionParticipant::where('election_id', $election->id)
            ->selectRaw('participantable_type, SUM(CASE WHEN can_vote THEN 1 ELSE 0 END) as voters, SUM(CASE WHEN can_apply THEN 1 ELSE 0 END) as applicants')
            ->groupBy('participantable_type')
            ->get();

        return [
            'election_id' => $election->id,
            'configuration' => $election->participants,
            'summary' => $counts->mapWithKeys(fn($row) => [
                ElectionParticipantType::keyFor($row->participantable_type) ?? $row->participantable_type => [
                    'voters' => (int) $row->voters,
                    'applicants' => (int) $row->applicants,
                ],
            ]),
            'total_voters' => (int) $counts->sum('voters'),
            'total_applicants' => (int) $counts->sum('applicants'),
        ];
    }

    /**
     * Replace the materialised rows with the resolved set, preserving the
     * row ids of participants who stay eligible.
     */
    private function syncRows(Election $election, array $rows): void
    {
        $now = Carbon::now();
        $existing = ElectionParticipant::where('election_id', $election->id)
            ->get(['id', 'participantable_type', 'participantable_id', 'can_vote', 'can_apply'])
            ->keyBy(fn($row) => $row->participantable_type . '|' . $row->participantable_id);

        $keep = [];
        $inserts = [];

        foreach ($rows as $class => $participants) {
            foreach ($participants as $id => $flags) {
                $key = $class . '|' . $id;
                $keep[$key] = true;
                $row = $existing->get($key);

                if ($row) {
                    if ($row->can_vote !== $flags['can_vote'] || $row->can_apply !== $flags['can_apply']) {
                        $row->update($flags);
                    }
                    continue;
                }

                $inserts[] = [
                    'id' => (string) Str::uuid(),
                    'school_branch_id' => $election->school_branch_id,
                    'election_id' => $election->id,
                    'participantable_type' => $class,
                    'participantable_id' => (string) $id,
                    'can_vote' => $flags['can_vote'],
                    'can_apply' => $flags['can_apply'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $stale = $existing->reject(fn($row, $key) => isset($keep[$key]))->pluck('id');
        $stale->chunk(1000)->each(fn($ids) => ElectionParticipant::whereIn('id', $ids->all())->delete());

        foreach (array_chunk($inserts, 1000) as $chunk) {
            ElectionParticipant::insert($chunk);
        }
    }

    private function assertConfigurable(Election $election): void
    {
        if ($election->isLocked()) {
            $this->fail(
                "Participants locked",
                "Participants can no longer be changed once voting has started or the election has ended.",
                409
            );
        }
    }
}
