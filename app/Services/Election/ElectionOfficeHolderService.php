<?php

namespace App\Services\Election;

use App\Models\AcademicYear\SchoolAcademicYear;
use App\Models\AcademicYear\SystemAcademicYear;
use App\Models\Election\Election;
use App\Models\Election\ElectionCandidate;
use App\Models\Election\ElectionOfficeHolder;
use App\Models\Election\ElectionOfficeYear;
use App\Models\Election\ElectionRole;
use App\Services\Election\Support\ElectionParticipantType;
use App\Services\Election\Support\FindsElectionRecords;
use App\Services\Election\Support\OfficeTenureResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Tracks the actual occupants of positions (elected, appointed, replacement)
 * and their tenure across specialty-specific school academic years.
 */
class ElectionOfficeHolderService
{
    use FindsElectionRecords;

    private const RELATIONS = [
        'holderable',
        'position:id,name,seats,election_type_id',
        'election:id,title,academic_year_id',
        'officeYears.schoolAcademicYear:id,specialty_id,start_date,end_date,system_academic_year_id',
        'officeYears.systemAcademicYear:id,name,year_start,year_end',
        'replacedHolder:id,holderable_type,holderable_id,status',
    ];

    public function __construct(
        private OfficeTenureResolver $tenureResolver,
        private ElectionEligibilityService $eligibility,
    ) {}

    /**
     * Winner of an election: tenure starts in the election's system academic year.
     */
    public function createElectedHolder(Election $election, ElectionRole $position, ElectionCandidate $candidate, int $voteCount): ElectionOfficeHolder
    {
        $holder = $candidate->candidateable;
        $term = $this->tenureResolver->termFor($position);
        $years = $this->tenureResolver->resolveSchoolYears(
            $holder,
            $election->school_branch_id,
            $election->systemAcademicYear,
            $term->duration_academic_years
        );

        return $this->createHolder([
            'school_branch_id' => $election->school_branch_id,
            'election_id' => $election->id,
            'election_role_id' => $position->id,
            'election_candidate_id' => $candidate->id,
            'holderable_type' => $holder->getMorphClass(),
            'holderable_id' => $holder->getKey(),
            'appointment_type' => ElectionOfficeHolder::APPOINTMENT_ELECTED,
            'vote_count' => $voteCount,
        ], $years);
    }

    /**
     * Direct appointment by an administrator (no election).
     */
    public function appointHolder(array $data, object $currentSchool): ElectionOfficeHolder
    {
        return DB::transaction(function () use ($data, $currentSchool) {
            $position = $this->findInBranch(ElectionRole::class, $data['election_role_id'], $currentSchool, ['officeTerm'], true);
            $holder = ElectionParticipantType::find($data['holder_type'], $data['holder_id'], $currentSchool->id);
            $startYear = $this->systemYear($data['system_academic_year_id']);
            $term = $this->tenureResolver->termFor($position);

            $years = !empty($data['school_academic_year_ids'])
                ? $this->tenureResolver->validateSchoolYears($data['school_academic_year_ids'], $holder, $currentSchool->id, $startYear, $term->duration_academic_years)
                : $this->tenureResolver->resolveSchoolYears($holder, $currentSchool->id, $startYear, $term->duration_academic_years);

            $this->assertSeatAvailable($position, $years);
            $this->assertTermLimits($position, $holder);

            return $this->createHolder([
                'school_branch_id' => $currentSchool->id,
                'election_id' => null,
                'election_role_id' => $position->id,
                'holderable_type' => $holder->getMorphClass(),
                'holderable_id' => $holder->getKey(),
                'appointment_type' => ElectionOfficeHolder::APPOINTMENT_APPOINTED,
                'remarks' => $data['remarks'] ?? null,
            ], $years)->load(self::RELATIONS);
        });
    }

    /**
     * End a holder's tenure early and hand the remaining tenure to a replacement.
     */
    public function replaceHolder(string $holderId, array $data, object $currentSchool): ElectionOfficeHolder
    {
        return DB::transaction(function () use ($holderId, $data, $currentSchool) {
            $current = $this->findInBranch(ElectionOfficeHolder::class, $holderId, $currentSchool, ['officeYears.schoolAcademicYear', 'officeYears.systemAcademicYear', 'position.officeTerm'], true);

            if (!in_array($current->status, ElectionOfficeHolder::OCCUPYING_STATUSES, true)) {
                $this->fail("Holder not in office", "Only scheduled or active office holders can be replaced.", 409);
            }

            $replacement = ElectionParticipantType::find($data['holder_type'], $data['holder_id'], $currentSchool->id);
            if ($replacement->getMorphClass() === $current->holderable_type && $replacement->getKey() === $current->holderable_id) {
                $this->fail("Invalid replacement", "An office holder cannot replace themselves.", 422);
            }

            $endedAt = Carbon::parse($data['ended_at'] ?? Carbon::today())->startOfDay();
            $remaining = $current->officeYears
                ->filter(fn(ElectionOfficeYear $year) => Carbon::parse($year->schoolAcademicYear->end_date)->greaterThanOrEqualTo($endedAt))
                ->values();

            if ($remaining->isEmpty()) {
                $this->fail("No remaining tenure", "The current holder's tenure has no remaining academic years to hand over.", 409);
            }

            $this->terminate($current, $endedAt, $data['termination_remarks'] ?? ($data['remarks'] ?? 'Replaced'));

            // Same system academic years, re-resolved for the replacement (their specialty's real dates).
            $years = $remaining->map(fn(ElectionOfficeYear $year) => $this->tenureResolver
                ->resolveSchoolYears($replacement, $currentSchool->id, $year->systemAcademicYear, 1)
                ->first());

            $this->assertTermLimits($current->position, $replacement);

            return $this->createHolder([
                'school_branch_id' => $currentSchool->id,
                'election_id' => $current->election_id,
                'election_role_id' => $current->election_role_id,
                'holderable_type' => $replacement->getMorphClass(),
                'holderable_id' => $replacement->getKey(),
                'appointment_type' => ElectionOfficeHolder::APPOINTMENT_REPLACEMENT,
                'replaces_holder_id' => $current->id,
                'remarks' => $data['remarks'] ?? null,
            ], $years, $endedAt)->load(self::RELATIONS);
        });
    }

    public function terminateHolder(string $holderId, array $data, object $currentSchool): ElectionOfficeHolder
    {
        return DB::transaction(function () use ($holderId, $data, $currentSchool) {
            $holder = $this->findInBranch(ElectionOfficeHolder::class, $holderId, $currentSchool, [], true);
            if (!in_array($holder->status, ElectionOfficeHolder::OCCUPYING_STATUSES, true)) {
                $this->fail("Holder not in office", "Only scheduled or active office holders can be terminated.", 409);
            }
            $this->terminate($holder, Carbon::parse($data['ended_at'] ?? Carbon::today()), $data['remarks'] ?? null);
            return $holder->fresh(self::RELATIONS);
        });
    }

    /**
     * Refresh tenure dates from the referenced school academic years and move
     * holders through scheduled → active → completed using those real dates.
     */
    public function syncStatuses(?string $schoolBranchId = null): int
    {
        $today = Carbon::today();
        $updated = 0;

        ElectionOfficeHolder::whereIn('status', ElectionOfficeHolder::OCCUPYING_STATUSES)
            ->when($schoolBranchId, fn($q) => $q->where('school_branch_id', $schoolBranchId))
            ->with('officeYears.schoolAcademicYear:id,start_date,end_date')
            ->chunkById(200, function ($holders) use ($today, &$updated) {
                foreach ($holders as $holder) {
                    $schoolYears = $holder->officeYears->pluck('schoolAcademicYear')->filter();
                    $startedAt = $holder->replaces_holder_id ? $holder->started_at : Carbon::parse($schoolYears->min('start_date') ?? $holder->started_at);
                    $expectedEnd = Carbon::parse($schoolYears->max('end_date') ?? $holder->expected_end_at);

                    $status = match (true) {
                        $today->lessThan($startedAt) => ElectionOfficeHolder::STATUS_SCHEDULED,
                        $today->greaterThan($expectedEnd) => ElectionOfficeHolder::STATUS_COMPLETED,
                        default => ElectionOfficeHolder::STATUS_ACTIVE,
                    };

                    $changes = array_filter([
                        'started_at' => $holder->started_at?->equalTo($startedAt) ? null : $startedAt,
                        'expected_end_at' => $holder->expected_end_at?->equalTo($expectedEnd) ? null : $expectedEnd,
                        'status' => $holder->status === $status ? null : $status,
                        'ended_at' => $status === ElectionOfficeHolder::STATUS_COMPLETED ? $expectedEnd : null,
                    ]);

                    if ($changes) {
                        $holder->update($changes);
                        $updated++;
                    }
                }
            });

        return $updated;
    }

    // ------------------------------------------------------------ queries

    public function getOfficeHolders(object $currentSchool, array $filters = [])
    {
        $this->syncStatuses($currentSchool->id);

        return ElectionOfficeHolder::where('school_branch_id', $currentSchool->id)
            ->when($filters['election_role_id'] ?? null, fn($q, $id) => $q->where('election_role_id', $id))
            ->when($filters['election_id'] ?? null, fn($q, $id) => $q->where('election_id', $id))
            ->when($filters['status'] ?? null, fn($q, $status) => $q->whereIn('status', (array) $status))
            ->when($filters['appointment_type'] ?? null, fn($q, $type) => $q->where('appointment_type', $type))
            ->with(self::RELATIONS)
            ->orderByDesc('started_at')
            ->get()
            ->map(fn($holder) => $this->present($holder));
    }

    public function getCurrentOfficeHolders(object $currentSchool)
    {
        return $this->getOfficeHolders($currentSchool, ['status' => [ElectionOfficeHolder::STATUS_ACTIVE]]);
    }

    public function getOfficeHolderDetails(string $holderId, object $currentSchool): array
    {
        $holder = $this->findInBranch(ElectionOfficeHolder::class, $holderId, $currentSchool, array_merge(self::RELATIONS, ['replacement.holderable']));
        return $this->present($holder) + ['replacement' => $holder->replacement ? $this->present($holder->replacement) : null];
    }

    /**
     * A participant's complete election history.
     */
    public function getParticipantHistory(object $currentSchool, string $type, string $id): array
    {
        $participant = ElectionParticipantType::find($type, $id, $currentSchool->id);

        return [
            'participant_type' => $type,
            'participant_id' => $participant->getKey(),
            'name' => ElectionParticipantType::displayName($participant),
            'applications' => $participant->electionApplications()->with(['election:id,title,status', 'position:id,name'])->latest()->get(),
            'candidacies' => $participant->electionCandidacies()->with(['election:id,title,status,is_results_published', 'position:id,name'])->latest()->get(),
            'elections_voted' => $participant->electionVotes()->distinct()->count('election_id'),
            'offices' => $participant->electionOfficeHolders()->with(self::RELATIONS)->orderByDesc('started_at')->get()
                ->map(fn($holder) => $this->present($holder)),
        ];
    }

    public function present(ElectionOfficeHolder $holder): array
    {
        return [
            'id' => $holder->id,
            'position' => $holder->position,
            'election' => $holder->election,
            'holder_type' => ElectionParticipantType::keyFor($holder->holderable_type),
            'holder_id' => $holder->holderable_id,
            'name' => ElectionParticipantType::displayName($holder->holderable),
            'holder' => $holder->holderable,
            'appointment_type' => $holder->appointment_type,
            'status' => $holder->status,
            'vote_count' => $holder->vote_count,
            'started_at' => $holder->started_at?->toDateString(),
            'expected_end_at' => $holder->expected_end_at?->toDateString(),
            'ended_at' => $holder->ended_at?->toDateString(),
            'replaces_holder_id' => $holder->replaces_holder_id,
            'remarks' => $holder->remarks,
            'tenure_years' => $holder->officeYears->map(fn(ElectionOfficeYear $year) => [
                'term_year_number' => $year->term_year_number,
                'system_academic_year' => $year->systemAcademicYear,
                'school_academic_year' => $year->schoolAcademicYear,
            ])->values(),
        ];
    }

    // ------------------------------------------------------------ helpers

    /**
     * @param Collection<int, SchoolAcademicYear> $years ordered tenure years
     */
    private function createHolder(array $attributes, Collection $years, ?Carbon $startOverride = null): ElectionOfficeHolder
    {
        $first = $years->first();
        $last = $years->last();
        $startedAt = $startOverride ?? Carbon::parse($first->start_date);
        $expectedEnd = Carbon::parse($last->end_date);
        $today = Carbon::today();

        $holder = ElectionOfficeHolder::create(array_merge($attributes, [
            'start_school_academic_year_id' => $first->id,
            'end_school_academic_year_id' => $last->id,
            'started_at' => $startedAt,
            'expected_end_at' => $expectedEnd,
            'status' => $today->lessThan($startedAt) ? ElectionOfficeHolder::STATUS_SCHEDULED : ElectionOfficeHolder::STATUS_ACTIVE,
        ]));

        foreach ($years->values() as $index => $year) {
            ElectionOfficeYear::create([
                'school_branch_id' => $holder->school_branch_id,
                'election_office_holder_id' => $holder->id,
                'school_academic_year_id' => $year->id,
                'system_academic_year_id' => $year->system_academic_year_id,
                'term_year_number' => $index + 1,
            ]);
        }

        return $holder;
    }

    private function terminate(ElectionOfficeHolder $holder, Carbon $endedAt, ?string $remarks): void
    {
        $holder->update([
            'status' => ElectionOfficeHolder::STATUS_TERMINATED,
            'ended_at' => $endedAt,
            'remarks' => trim(($holder->remarks ? $holder->remarks . "\n" : '') . ($remarks ?? '')) ?: null,
        ]);
    }

    private function assertSeatAvailable(ElectionRole $position, Collection $years): void
    {
        $start = Carbon::parse($years->first()->start_date);
        $end = Carbon::parse($years->last()->end_date);

        $occupied = ElectionOfficeHolder::where('election_role_id', $position->id)
            ->occupying()
            ->whereDate('started_at', '<=', $end)
            ->whereDate('expected_end_at', '>=', $start)
            ->count();

        if ($occupied >= max(1, $position->seats)) {
            $this->fail(
                "Position fully occupied",
                "All {$position->seats} seat(s) of {$position->name} are occupied during this tenure. Replace or terminate a holder first.",
                409
            );
        }
    }

    private function assertTermLimits(ElectionRole $position, Model $holder): void
    {
        // Appointments are not tied to an election; an unsaved election never matches.
        $violations = $this->eligibility->officeTermViolations(new Election(), $position, $holder);
        if ($violations) {
            $this->fail("Term limit reached", implode(' ', $violations), 409);
        }
    }

    private function systemYear(string $id): SystemAcademicYear
    {
        $year = SystemAcademicYear::find($id);
        if (!$year) {
            $this->fail("Academic year not found", "The selected system academic year could not be found.", 404);
        }
        return $year;
    }
}
