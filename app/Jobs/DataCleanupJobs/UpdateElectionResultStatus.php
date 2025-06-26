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
use Illuminate\Support\Facades\Log;

class UpdateElectionResultStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The ID of the election.
     *
     * @var string
     */
    protected string $electionId;

    /**
     * The ID of the school branch.
     *
     * @var string
     */
    protected string $schoolBranchId;

    /**
     * Create a new job instance.
     *
     * @param string $electionId
     * @param string $schoolBranchId
     */
    public function __construct(string $electionId, string $schoolBranchId)
    {
        $this->electionId = $electionId;
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     * This method orchestrates the entire election power handover process.
     */
    public function handle(): void
    {

        DB::transaction(function () {
            // 1. Fetch the election details
            $election = Elections::where("school_branch_id", $this->schoolBranchId)
                                 ->with(['electionType'])
                                  ->find($this->electionId);

            if (!$election) {
                Log::warning("Election not found for ID: {$this->electionId} in school branch: {$this->schoolBranchId}. Aborting power handover.");
                return;
            }

            // 2. Determine winners from election results and update their status (won/lost)
            $allElectionResults = ElectionResults::where("school_branch_id", $this->schoolBranchId)
                                                  ->where("election_id", $this->electionId)
                                                  ->get();

            if ($allElectionResults->isEmpty()) {
                Log::info("No election results found for election ID: {$this->electionId}. No winners to determine or assign.");
                return;
            }

            $groupedResultsByPosition = $allElectionResults->groupBy('position_id');
            $newWinnersElectionResults = collect();

            foreach ($groupedResultsByPosition as $positionId => $resultsForPosition) {
                $winnersForPosition = $this->determineAndMarkWinnerStatus($resultsForPosition);
                $newWinnersElectionResults = $newWinnersElectionResults->merge($winnersForPosition);
            }

            // 3. Transition previous winners to past winners, revoke their roles and badges.
            $this->transitionCurrentWinnersToPast($election->election_type_id, $this->schoolBranchId);

            // 4. Assign roles and badges to the newly determined winners
            $this->assignNewWinnerRolesAndBadges($newWinnersElectionResults, $election->election_type_id, $this->schoolBranchId);

            Log::info("Election power handover completed successfully for election ID: {$this->electionId} in school branch: {$this->schoolBranchId}.");

            foreach($newWinnersElectionResults as $winners){
               $electionName = $election->electionType->election_name;
               $role = ElectionRoles::find($winners->position_id);
               $student = Student::find($winners->student_id);
               $student->notify( new ElectionWinner( $role->name, $electionName));
            }

        });
    }

    /**
     * Determines the winner(s) for a given set of election results for a single position.
     * Updates the 'status' field for each result (won/lost) in the database.
     * Returns a Collection of the ElectionResults models that were marked as 'won'.
     *
     * @param Collection $resultsForPosition A collection of ElectionResults models for a single position.
     * @return Collection A collection of ElectionResults models that are winners for this position.
     */
    private function determineAndMarkWinnerStatus(Collection $resultsForPosition): Collection
    {
        if ($resultsForPosition->isEmpty()) {
            return collect();
        }

        $maxVotes = $resultsForPosition->max('vote_count');
        $winnersForThisPosition = collect();

        foreach ($resultsForPosition as $result) {
            if ($result->vote_count === $maxVotes) {
                if ($result->status !== 'won') {
                    $result->update(['status' => 'won']);
                }
                $winnersForThisPosition->push($result);
            } else {
                if ($result->status !== 'lost') {
                    $result->update(['status' => 'lost']);
                }
            }
        }

        return $winnersForThisPosition;
    }

    /**
     * Transitions existing current winners for an election type to past winners.
     * Revokes their assigned roles and badges, then deletes them from CurrentElectionWinners.
     *
     * @param string $electionTypeId The ID of the election type whose current winners are being transitioned.
     * @param string $schoolBranchId The ID of the school branch.
     */
    private function transitionCurrentWinnersToPast(string $electionTypeId, string $schoolBranchId): void
    {
        $currentWinners = CurrentElectionWinners::where("school_branch_id", $schoolBranchId)
                                                ->where("election_type_id", $electionTypeId)
                                                ->with(['student', 'electionRole'])
                                                ->get();

        if ($currentWinners->isEmpty()) {
            Log::info("No current winners to transition for election type ID: {$electionTypeId} in school branch: {$schoolBranchId}.");
            return;
        }

        $goldBadge = Badge::where("color", "gold")->first();
        $blueBadge = Badge::where("color", "blue")->first();

        if (!$goldBadge) {
            Log::error("Gold badge not found. Cannot revoke gold badges from past winners.");
        }
        if (!$blueBadge) {
            Log::error("Blue badge not found. Cannot assign blue badges to past winners.");
        }

        foreach ($currentWinners as $pastWinner) {
            if (!$pastWinner->student) {
                Log::warning("Student not found for past winner ID: {$pastWinner->id}. Skipping role/badge revocation.");
                continue;
            }
            if (!$pastWinner->electionRole) {
                Log::warning("Election role not found for past winner ID: {$pastWinner->id}. Skipping role revocation.");
                continue;
            }

            $roleToRevoke = Role::findByName($pastWinner->electionRole->name);
            if ($roleToRevoke && $pastWinner->student->hasRole($roleToRevoke->name)) {
                $pastWinner->student->removeRole($roleToRevoke->name);
                Log::info("Removed role '{$roleToRevoke->name}' from student ID: {$pastWinner->student->id}.");
            } else if ($roleToRevoke) {
                Log::info("Student ID: {$pastWinner->student->id} did not have role '{$roleToRevoke->name}' to remove.");
            } else {
                Log::warning("Role model not found for election role '{$pastWinner->electionRole->name}'. Cannot revoke role.");
            }

            if ($goldBadge) {
                BadgeAssignment::where("assignable_id", $pastWinner->student->id)
                               ->where("assignable_type", get_class($pastWinner->student))
                               ->where("badge_id", $goldBadge->id)
                               ->delete();
                Log::info("Removed gold badge from student ID: {$pastWinner->student->id}.");
            }

            if ($blueBadge) {
                $existingBlueBadge = BadgeAssignment::where("assignable_id", $pastWinner->student->id)
                                                    ->where("assignable_type", get_class($pastWinner->student))
                                                    ->where("badge_id", $blueBadge->id)
                                                    ->first();
                if (!$existingBlueBadge) {
                    BadgeAssignment::create([
                        'assignable_id' => $pastWinner->student->id,
                        'assignable_type' => get_class($pastWinner->student),
                        'badge_id' => $blueBadge->id,
                    ]);
                    Log::info("Assigned blue badge to student ID: {$pastWinner->student->id}.");
                } else {
                    Log::info("Student ID: {$pastWinner->student->id} already has a blue badge.");
                }
            }

            PastElectionWinners::create([
                'total_votes' => $pastWinner->total_votes,
                'election_type_id' => $pastWinner->election_type_id,
                'election_role_id' => $pastWinner->election_role_id,
                'student_id' => $pastWinner->student_id,
                'school_branch_id' => $pastWinner->school_branch_id,
                'year' => now()->year,
            ]);
            Log::info("Moved past winner ID: {$pastWinner->id} to PastElectionWinners.");

            $pastWinner->delete();
            Log::info("Deleted current winner record for ID: {$pastWinner->id}.");
        }
    }

    /**
     * Assigns roles and badges to the newly determined election winners.
     * Creates records in the CurrentElectionWinners table.
     *
     * @param Collection $newWinnersElectionResults A collection of ElectionResults models that are the new winners.
     * @param string $electionTypeId The ID of the election type these winners belong to.
     * @param string $schoolBranchId The ID of the school branch.
     */
    private function assignNewWinnerRolesAndBadges(
        Collection $newWinnersElectionResults,
        string $electionTypeId,
        string $schoolBranchId
    ): void {
        $electionRoles = ElectionRoles::where("election_type_id", $electionTypeId)
                                      ->where("school_branch_id", $schoolBranchId)
                                      ->get()
                                      ->keyBy('position_id');

        $goldBadge = Badge::where("color", "gold")->first();

        if (!$goldBadge) {
            Log::error("Gold badge not found. Cannot assign gold badges to new winners.");
        }

        foreach ($newWinnersElectionResults as $winnerResult) {
            $student = Student::find($winnerResult->student_id);
            if (!$student) {
                Log::warning("Student not found for winner result ID: {$winnerResult->id}. Skipping role/badge assignment.");
                continue;
            }

            $electionRole = $electionRoles->get($winnerResult->position_id);

            if (!$electionRole) {
                Log::warning("Election role not found for position ID: {$winnerResult->position_id}. Skipping role/badge assignment for student ID: {$student->id}.");
                continue;
            }

            $roleToAssign = Role::findByName($electionRole->name);

            if ($roleToAssign) {
                if (!$student->hasRole($roleToAssign->name)) {
                    $student->assignRole($roleToAssign->name);
                    Log::info("Assigned role '{$roleToAssign->name}' to student ID: {$student->id}.");
                } else {
                    Log::info("Student ID: {$student->id} already has role '{$roleToAssign->name}'.");
                }
            } else {
                Log::warning("Role model not found for election role name: '{$electionRole->name}'. Cannot assign role to student ID: {$student->id}.");
            }

            if ($goldBadge) {
                BadgeAssignment::where("assignable_id", $student->id)
                               ->where("assignable_type", get_class($student))
                               ->where("badge_id", $goldBadge->id)
                               ->delete();

                BadgeAssignment::create([
                    'assignable_id' => $student->id,
                    'assignable_type' => get_class($student),
                    'badge_id' => $goldBadge->id,
                ]);
                Log::info("Assigned gold badge to student ID: {$student->id}.");
            }

            CurrentElectionWinners::create([
                'total_votes' => $winnerResult->vote_count,
                'election_type_id' => $electionTypeId,
                'election_role_id' => $electionRole->id,
                'student_id' => $student->id,
                'school_branch_id' => $schoolBranchId,
                'election_id' => $this->electionId,
            ]);
            Log::info("Created CurrentElectionWinners record for student ID: {$student->id} (ElectionResult ID: {$winnerResult->id}).");
        }
    }
}
