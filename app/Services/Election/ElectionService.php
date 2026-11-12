<?php

namespace App\Services\Election;

use App\Jobs\NotificationJobs\SendAdminElectionConcludedNotificationJob;
use App\Jobs\NotificationJobs\SendElectionConcludedNotificationJob;
use App\Jobs\NotificationJobs\SendElectionOpenNotificationJob;
use App\Jobs\NotificationJobs\SendElectionVoteOpenNotification;
use App\Models\AcademicYear\SystemAcademicYear;
use App\Models\Election\Election;
use App\Models\Election\ElectionParticipant;
use App\Models\Election\ElectionRole;
use App\Models\Election\ElectionType;
use App\Services\Election\Support\ElectionParticipantType;
use App\Services\Election\Support\FindsElectionRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Election configuration and lifecycle.
 *
 * draft ──publish──▶ upcoming ─▶ application_open ─▶ application_closed ─▶ voting_open ─▶ voting_closed ──publish results──▶ completed
 *   └──────────────────────────────── cancel ───────────────────────────────────────────────┘
 * Date-driven transitions are applied by syncStatuses() (scheduled command)
 * and lazily whenever an election is acted on.
 */
class ElectionService
{
    use FindsElectionRecords;

    private const CONFIG_FIELDS = [
        'title',
        'description',
        'application_start',
        'application_end',
        'voting_start',
        'voting_end',
        'max_applications_per_participant',
        'allow_candidate_self_vote',
    ];

    public function __construct(private ElectionParticipantService $participantService) {}

    public function createElection(array $data, object $currentSchool): Election
    {
        $electionType = $this->findInBranch(ElectionType::class, $data['election_type_id'], $currentSchool);
        if ($electionType->status !== 'active') {
            $this->fail("Election type inactive", "Activate the election type before creating an election for it.");
        }

        $systemYear = $this->systemYear($data['system_academic_year_id']);
        $this->assertNoDuplicate($currentSchool, $electionType->id, $systemYear->id);
        $this->assertSchedule($data, $systemYear);

        return DB::transaction(function () use ($data, $currentSchool, $electionType, $systemYear) {
            $election = Election::create(array_merge(Arr::only($data, self::CONFIG_FIELDS), [
                'title' => $data['title'] ?? "{$electionType->election_title} {$systemYear->name}",
                'status' => Election::STATUS_DRAFT,
                'election_type_id' => $electionType->id,
                'academic_year_id' => $systemYear->id,
                'school_branch_id' => $currentSchool->id,
            ]));

            if (!empty($data['participants'])) {
                $this->participantService->configureParticipants($currentSchool, $election->id, $data['participants']);
            }

            return $election->fresh(['electionType', 'systemAcademicYear']);
        });
    }

    public function updateElection(array $data, string $electionId, object $currentSchool): Election
    {
        $election = $this->findInBranch(Election::class, $electionId, $currentSchool);
        $this->sync($election);

        if ($election->isLocked()) {
            $this->fail("Election locked", "An election cannot be edited once voting has started, it has ended or it was cancelled.", 409);
        }

        $attributes = Arr::only(array_filter($data, fn($v) => $v !== null), array_merge(self::CONFIG_FIELDS, ['election_type_id', 'system_academic_year_id']));

        $structural = array_intersect(array_keys($attributes), ['election_type_id', 'system_academic_year_id']);
        if ($structural && $election->applications()->exists()) {
            $this->fail("Election has applications", "The election type and academic year cannot be changed after applications have been submitted.", 409);
        }

        if (isset($attributes['election_type_id'])) {
            $this->findInBranch(ElectionType::class, $attributes['election_type_id'], $currentSchool);
        }

        $systemYear = $this->systemYear($attributes['system_academic_year_id'] ?? $election->academic_year_id);
        if (isset($attributes['system_academic_year_id'])) {
            $attributes['academic_year_id'] = $systemYear->id;
            unset($attributes['system_academic_year_id']);
        }
        if ($structural) {
            $this->assertNoDuplicate($currentSchool, $attributes['election_type_id'] ?? $election->election_type_id, $systemYear->id, $election->id);
        }

        $schedule = array_merge(
            Arr::only($election->only(['application_start', 'application_end', 'voting_start', 'voting_end']), ['application_start', 'application_end', 'voting_start', 'voting_end']),
            Arr::only($attributes, ['application_start', 'application_end', 'voting_start', 'voting_end'])
        );
        $this->assertSchedule($schedule, $systemYear, $election);

        return DB::transaction(function () use ($election, $attributes) {
            $election->update($attributes);
            if ($election->status !== Election::STATUS_DRAFT) {
                $this->sync($election);
            }
            return $election->fresh(['electionType', 'systemAcademicYear']);
        });
    }

    public function bulkUpdateElections(array $items, object $currentSchool): array
    {
        return DB::transaction(fn() => collect($items)->map(
            fn($item) => $this->updateElection(Arr::except($item, 'election_id'), $item['election_id'], $currentSchool)
        )->all());
    }

    public function deleteElection(string $electionId, object $currentSchool): Election
    {
        $election = $this->findInBranch(Election::class, $electionId, $currentSchool);

        if (!in_array($election->status, [Election::STATUS_DRAFT, Election::STATUS_CANCELLED], true)
            || $election->votes()->exists()
            || $election->officeHolders()->exists()) {
            $this->fail(
                "Election cannot be deleted",
                "Only draft or cancelled elections without votes or office holders can be deleted. Cancel the election to keep its history.",
                409
            );
        }

        DB::transaction(function () use ($election) {
            $election->candidates()->delete();
            $election->applications()->delete();
            $election->electionParticipants()->delete();
            $election->delete();
        });

        return $election;
    }

    public function bulkDeleteElections(array $electionIds, object $currentSchool): array
    {
        return DB::transaction(fn() => collect($electionIds)
            ->map(fn($id) => $this->deleteElection($id, $currentSchool))->all());
    }

    // ------------------------------------------------------------ queries

    public function getElections(object $currentSchool, array $filters = [])
    {
        $this->syncStatuses($currentSchool->id);

        return Election::where('school_branch_id', $currentSchool->id)
            ->when($filters['status'] ?? null, fn($q, $status) => $q->whereIn('status', (array) $status))
            ->when($filters['election_type_id'] ?? null, fn($q, $id) => $q->where('election_type_id', $id))
            ->when($filters['system_academic_year_id'] ?? null, fn($q, $id) => $q->where('academic_year_id', $id))
            ->with(['electionType:id,election_title', 'systemAcademicYear:id,name,year_start,year_end'])
            ->withCount(['applications', 'candidates', 'votes'])
            ->orderByDesc('voting_start')
            ->get();
    }

    public function getPastElections(object $currentSchool)
    {
        return $this->getElections($currentSchool, ['status' => [Election::STATUS_COMPLETED, Election::STATUS_CANCELLED]]);
    }

    public function getElectionDetails(string $electionId, object $currentSchool): array
    {
        $election = $this->findInBranch(Election::class, $electionId, $currentSchool, ['electionType', 'systemAcademicYear']);
        $this->sync($election);

        $positions = ElectionRole::where('school_branch_id', $currentSchool->id)
            ->where('election_type_id', $election->election_type_id)
            ->with('officeTerm')
            ->withCount([
                'applications as applications_count' => fn($q) => $q->where('election_id', $election->id),
                'candidates as candidates_count' => fn($q) => $q->where('election_id', $election->id)->where('status', 'active'),
            ])
            ->get();

        return [
            'election' => $election->loadCount(['applications', 'candidates', 'votes']),
            'positions' => $positions,
            'participants' => $this->participantService->summary($election),
            'turnout' => $this->turnout($election),
        ];
    }

    /**
     * Elections the authenticated participant is eligible for, with what they can do now.
     */
    public function getParticipantElections(object $currentSchool, Model $participant)
    {
        $this->syncStatuses($currentSchool->id);

        $rows = ElectionParticipant::where('school_branch_id', $currentSchool->id)
            ->where('participantable_type', $participant->getMorphClass())
            ->where('participantable_id', $participant->getKey())
            ->whereHas('election', fn($q) => $q->whereNotIn('status', [Election::STATUS_DRAFT, Election::STATUS_CANCELLED]))
            ->with('election.electionType:id,election_title,description')
            ->get();

        $votedElectionIds = $participant->electionVotes()->distinct()->pluck('election_id')->flip();

        return $rows->map(function (ElectionParticipant $row) use ($votedElectionIds) {
            $election = $row->election;
            return [
                'election_id' => $election->id,
                'title' => $election->title ?? $election->electionType?->election_title,
                'description' => $election->description ?? $election->electionType?->description,
                'status' => $election->status,
                'application_start' => $election->application_start,
                'application_end' => $election->application_end,
                'voting_start' => $election->voting_start,
                'voting_end' => $election->voting_end,
                'can_vote' => $row->can_vote,
                'can_apply' => $row->can_apply,
                'application_open' => $row->can_apply && $election->isApplicationWindowOpen(),
                'voting_open' => $row->can_vote && $election->isVotingWindowOpen(),
                'has_voted' => $votedElectionIds->has($election->id),
                'results_published' => (bool) $election->is_results_published,
            ];
        })->sortByDesc('voting_start')->values();
    }

    // ---------------------------------------------------------- lifecycle

    public function publishElection(string $electionId, object $currentSchool): Election
    {
        $election = $this->findInBranch(Election::class, $electionId, $currentSchool);

        if ($election->status !== Election::STATUS_DRAFT) {
            $this->fail("Election already published", "Only draft elections can be published.", 409);
        }

        $activePositions = ElectionRole::where('school_branch_id', $currentSchool->id)
            ->where('election_type_id', $election->election_type_id)
            ->where('status', 'active')
            ->count();
        if ($activePositions === 0) {
            $this->fail("No positions configured", "Add at least one active position to the election type before publishing.");
        }

        $summary = $this->participantService->summary($election);
        if ($summary['total_voters'] === 0 || $summary['total_applicants'] === 0) {
            $this->fail("Participants not configured", "Configure who can vote and who can apply before publishing the election.");
        }

        if ($election->voting_end && $election->voting_end->isPast()) {
            $this->fail("Schedule in the past", "The voting period has already ended. Update the schedule before publishing.");
        }

        $status = $election->statusForDate();
        $election->update(['status' => $status]);
        $this->notifyTransition($election, $status);
        return $election->fresh(['electionType', 'systemAcademicYear']);
    }

    public function cancelElection(string $electionId, object $currentSchool): Election
    {
        $election = $this->findInBranch(Election::class, $electionId, $currentSchool);

        if (in_array($election->status, [Election::STATUS_COMPLETED, Election::STATUS_CANCELLED], true)) {
            $this->fail("Election cannot be cancelled", "Completed or already cancelled elections cannot be cancelled.", 409);
        }

        $election->update(['status' => Election::STATUS_CANCELLED]);
        return $election;
    }

    public function closeVoting(string $electionId, object $currentSchool): Election
    {
        $election = $this->findInBranch(Election::class, $electionId, $currentSchool);
        $this->sync($election);

        if ($election->status !== Election::STATUS_VOTING_OPEN) {
            $this->fail("Voting not open", "Voting can only be closed while it is open.", 409);
        }

        $election->update(['status' => Election::STATUS_VOTING_CLOSED, 'voting_end' => Carbon::now()]);
        return $election;
    }

    /**
     * Apply date-driven transitions to every scheduled election (optionally per branch).
     */
    public function syncStatuses(?string $schoolBranchId = null): int
    {
        $updated = 0;
        Election::whereIn('status', Election::SCHEDULED_STATUSES)
            ->when($schoolBranchId, fn($q) => $q->where('school_branch_id', $schoolBranchId))
            ->chunkById(200, function ($elections) use (&$updated) {
                foreach ($elections as $election) {
                    $updated += $this->sync($election) ? 1 : 0;
                }
            });
        return $updated;
    }

    public function sync(Election $election): bool
    {
        if (!in_array($election->status, Election::SCHEDULED_STATUSES, true)) {
            return false;
        }
        $status = $election->statusForDate();
        if ($status === $election->status) {
            return false;
        }
        $election->update(['status' => $status]);
        $this->notifyTransition($election, $status);
        return true;
    }

    /**
     * Notify participants when an election reaches a milestone. Notifications
     * are queued after the surrounding transaction commits.
     */
    public function notifyTransition(Election $election, string $status): void
    {
        $jobs = match ($status) {
            Election::STATUS_APPLICATION_OPEN => [SendElectionOpenNotificationJob::class],
            Election::STATUS_VOTING_OPEN => [SendElectionVoteOpenNotification::class],
            Election::STATUS_COMPLETED => [SendElectionConcludedNotificationJob::class, SendAdminElectionConcludedNotificationJob::class],
            default => [],
        };

        foreach ($jobs as $job) {
            try {
                $job::dispatch($election->id, $election->school_branch_id)->afterCommit();
            } catch (Throwable $e) {
                Log::warning('Election notification dispatch failed', ['job' => $job, 'message' => $e->getMessage()]);
            }
        }
    }

    // ------------------------------------------------------------ helpers

    public function turnout(Election $election): array
    {
        $eligible = $election->voters()->count();
        $voted = $election->votes()->distinct()->count(DB::raw("CONCAT(votable_type, '|', votable_id)"));

        return [
            'eligible_voters' => $eligible,
            'voters_participated' => $voted,
            'turnout_percentage' => $eligible ? round($voted / $eligible * 100, 2) : 0,
        ];
    }

    private function systemYear(string $id): SystemAcademicYear
    {
        $year = SystemAcademicYear::find($id);
        if (!$year) {
            $this->fail("Academic year not found", "The selected system academic year could not be found.", 404);
        }
        return $year;
    }

    private function assertNoDuplicate(object $currentSchool, string $electionTypeId, string $systemYearId, ?string $ignoreId = null): void
    {
        $exists = Election::where('school_branch_id', $currentSchool->id)
            ->where('election_type_id', $electionTypeId)
            ->where('academic_year_id', $systemYearId)
            ->where('status', '!=', Election::STATUS_CANCELLED)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if ($exists) {
            $this->fail("Election already exists", "An election of this type already exists for the selected academic year.", 409);
        }
    }

    private function assertSchedule(array $schedule, SystemAcademicYear $systemYear, ?Election $election = null): void
    {
        $dates = collect(['application_start', 'application_end', 'voting_start', 'voting_end'])
            ->mapWithKeys(fn($key) => [$key => empty($schedule[$key]) ? null : Carbon::parse($schedule[$key])]);

        if ($dates->contains(null)) {
            $this->fail("Incomplete schedule", "Application and voting start and end dates are all required.");
        }

        if (!($dates['application_start'] < $dates['application_end']
            && $dates['application_end'] <= $dates['voting_start']
            && $dates['voting_start'] < $dates['voting_end'])) {
            $this->fail(
                "Invalid schedule",
                "The application period must end before voting starts, and each period must end after it starts."
            );
        }

        // The election must take place within the selected academic year.
        if ($dates['application_start']->year < (int) $systemYear->year_start
            || $dates['voting_end']->year > (int) $systemYear->year_end) {
            $this->fail(
                "Schedule outside academic year",
                "The election schedule must fall within the {$systemYear->name} academic year ({$systemYear->year_start}–{$systemYear->year_end})."
            );
        }

        // Once applications opened, their start date is history.
        if ($election && $election->applications()->exists()
            && !$dates['application_start']->equalTo($election->application_start)) {
            $this->fail("Application period already started", "The application start date cannot change after applications were submitted.", 409);
        }
    }
}
