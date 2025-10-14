<?php

namespace App\Jobs\DataCleanupJobs;

use App\Models\Badge;
use App\Models\BadgeAssignment;
use App\Models\CurrentElectionWinners;
use App\Models\ElectionResults;
use App\Models\ElectionRoles;
use App\Models\Elections;
use App\Models\PastElectionWinners;
use App\Models\Role;
use App\Models\Student;
use App\Notifications\ElectionWinner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\PermissionCategory;
use App\Models\Schooladmin;
use App\Notifications\AdminElectionTransition;
use Illuminate\Support\Facades\Notification;
class UpdateElectionResultStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $electionId;
    protected string $schoolBranchId;

    public function __construct(string $electionId, string $schoolBranchId)
    {
        $this->electionId = $electionId;
        $this->schoolBranchId = $schoolBranchId;
    }

    public function handle(): void
    {
        $electionDetails = Elections::query()
            ->where('school_branch_id', $this->schoolBranchId)
            ->with('electionType')
            ->findOrFail($this->electionId);

        $electionResults = ElectionResults::query()
            ->where('school_branch_id', $this->schoolBranchId)
            ->where('election_id', $this->electionId)
            ->with('electionCandidate.student')
            ->get();

        $electionWinners = $this->processElectionResults($electionResults);

        DB::transaction(function () use ($electionWinners, $electionDetails) {
            $this->transitionCurrentWinnersToPast($electionDetails, $this->schoolBranchId);
            $this->assignNewWinnerRolesAndBadges(
                $electionWinners,
                $electionDetails,
                $this->schoolBranchId
            );
            $this->notifyWinners($electionWinners, $electionDetails->electionType->election_title);
        });

        $schoolAdminsToNotify = $this->getElectionManagers($this->schoolBranchId);
        Notification::send($schoolAdminsToNotify, new AdminElectionTransition($electionDetails->electionType->election_title));

    }

    private function processElectionResults(Collection $allElectionResults): Collection
    {
        $groupedResults = $allElectionResults->groupBy('position_id');
        $newWinners = collect();

        foreach ($groupedResults as $results) {
            if ($results->isEmpty()) {
                continue;
            }

            $maxVotes = $results->max('vote_count');

            foreach ($results as $result) {
                $newStatus = $result->vote_count === $maxVotes ? 'won' : 'lost';

                if ($result->election_status !== $newStatus) {
                    $result->update(['election_status' => $newStatus]);
                }

                if ($newStatus === 'won') {
                    $newWinners->push($result);
                }
            }
        }

        return $newWinners;
    }

    private function transitionCurrentWinnersToPast($electionDetails, string $schoolBranchId): void
    {
        $currentWinners = CurrentElectionWinners::query()
            ->where('school_branch_id', $schoolBranchId)
            ->where('election_type_id', $electionDetails->election_type_id)
            ->with(['student', 'electionRole'])
            ->get();

        if ($currentWinners->isEmpty()) {
            return;
        }

        $goldBadge = Badge::where('name', 'Golden')->first();
        $blueBadge = Badge::where('name', 'Blue')->first();

        foreach ($currentWinners as $winner) {
            if (!$winner->student || !$winner->electionRole) {
                continue;
            }

            $roleToRevoke = Role::findByName($winner->electionRole->name, 'student');
            if ($roleToRevoke) {
                $winner->student->removeRole($roleToRevoke->name);
            }

            if ($goldBadge) {
                BadgeAssignment::where([
                    'assignable_id' => $winner->student->id,
                    'assignable_type' => get_class($winner->student),
                    'badge_id' => $goldBadge->id,
                ])->delete();
            }

            if ($blueBadge) {
                BadgeAssignment::firstOrCreate([
                    'assignable_id' => $winner->student->id,
                    'assignable_type' => get_class($winner->student),
                    'badge_id' => $blueBadge->id,
                ]);
            }

            PastElectionWinners::create([
                'total_votes' => $winner->total_votes,
                'election_type_id' => $winner->election_type_id,
                'election_role_id' => $winner->election_role_id,
                'election_id' => $electionDetails->id,
                'student_id' => $winner->student->id,
                'school_branch_id' => $winner->school_branch_id,
            ]);

            $winner->delete();
        }
    }

    private function assignNewWinnerRolesAndBadges(
        Collection $newWinnersElectionResults,
        $electionDetails,
        string $schoolBranchId
    ): void {
        $goldBadge = Badge::where('name', 'Golden')->first();

        foreach ($newWinnersElectionResults as $winnerResult) {
            $student = Student::find($winnerResult->electionCandidate->student->id);
            $electionRole = ElectionRoles::query()
                ->where('school_branch_id', $winnerResult->school_branch_id)
                ->find($winnerResult->position_id);

            if (!$student || !$electionRole) {
                continue;
            }

            $roleToAssign = Role::findByName($electionRole->name, 'student');
            if ($roleToAssign && !$student->hasRole($roleToAssign->name)) {
                $student->assignRole($roleToAssign->name);
            }

            if ($goldBadge) {
                BadgeAssignment::where([
                    'assignable_id' => $student->id,
                    'assignable_type' => get_class($student),
                    'badge_id' => $goldBadge->id,
                ])->delete();

                BadgeAssignment::create([
                    'assignable_id' => $student->id,
                    'assignable_type' => get_class($student),
                    'badge_id' => $goldBadge->id,
                ]);
            }

            CurrentElectionWinners::create([
                'total_votes' => $winnerResult->vote_count,
                'election_type_id' => $electionDetails->election_type_id,
                'election_role_id' => $electionRole->id,
                'student_id' => $student->id,
                'school_branch_id' => $schoolBranchId,
                'election_id' => $electionDetails->id,
            ]);
        }
    }

    private function notifyWinners(Collection $newWinnersElectionResults, string $electionName): void
    {
        foreach ($newWinnersElectionResults as $winnerResult) {
            $student = Student::find($winnerResult->electionCandidate->student->id);
            $electionRole = ElectionRoles::query()
                ->where('school_branch_id', $winnerResult->school_branch_id)
                ->find($winnerResult->position_id);

            if ($student && $electionRole) {
                $student->notify(new ElectionWinner($electionRole->name, $electionName));
            }
        }
    }

    private function getElectionManagers(string $schoolBranchId): Collection
    {
        $electionPermissionNames = PermissionCategory::with('permission')
            ->where('title', 'Election Manager')
            ->first()
            ?->permission
            ->pluck('name')
            ->toArray();

        if (empty($electionPermissionNames)) {
            return collect();
        }

        return Schooladmin::where('school_branch_id', $schoolBranchId)
            ->get()
            ->filter(fn($admin) => $admin->hasAnyPermission($electionPermissionNames));
    }
}
