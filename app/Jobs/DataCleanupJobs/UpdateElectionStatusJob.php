<?php

namespace App\Jobs\DataCleanupJobs;

use App\Models\Elections;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon; // Explicitly import Carbon for clarity

class UpdateElectionStatusJob implements ShouldQueue
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
     * @param string $electionId The ID of the election to update.
     * @param string $schoolBranchId The ID of the school branch the election belongs to.
     */
    public function __construct(string $electionId, string $schoolBranchId)
    {
        $this->electionId = $electionId;
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $election = Elections::where("school_branch_id", $this->schoolBranchId)->find($this->electionId);

        if (!$election) {
            return;
        }

        $currentTime = Carbon::now();

        $applicationStatus = $this->determineApplicationStatus($election, $currentTime);

        $votingStatus = $this->determineVotingStatus($election, $currentTime);

        $overallStatus = $this->determineOverallStatus($applicationStatus, $votingStatus);


        $election->update([
            'application_status' => $applicationStatus,
            'voting_status' => $votingStatus,
            'status' => $overallStatus,
        ]);
    }

    /**
     * Determines the application status of an election.
     *
     * @param Elections $election The election model instance.
     * @param Carbon $currentTime The current Carbon instance.
     * @return string 'upcoming', 'ongoing', or 'ended'.
     */
    private function determineApplicationStatus(Elections $election, Carbon $currentTime): string
    {

        if ($election->application_end->lt($currentTime)) {
            return 'ended';
        }

        if ($election->application_start->gt($currentTime)) {
            return 'upcoming';
        }

        return 'ongoing';
    }

    /**
     * Determines the voting status of an election.
     *
     * @param Elections $election The election model instance.
     * @param Carbon $currentTime The current Carbon instance.
     * @return string 'upcoming', 'ongoing', or 'ended'.
     */
    private function determineVotingStatus(Elections $election, Carbon $currentTime): string
    {
        if ($election->voting_end->lt($currentTime)) {
            return 'ended';
        }

        if ($election->voting_start->gt($currentTime)) {
            return 'upcoming';
        }

        return 'ongoing';
    }

    /**
     * Determines the overall election status based on application and voting statuses.
     *
     * @param string $applicationStatus The determined application status.
     * @param string $votingStatus The determined voting status.
     * @return string 'upcoming', 'ongoing', 'finished', or 'pending'.
     */
    private function determineOverallStatus(string $applicationStatus, string $votingStatus): string
    {
        if ($votingStatus === 'ongoing') {
            return 'ongoing';
        }

        if ($votingStatus === 'ended' && $applicationStatus === 'ended') {
            return 'finished';
        }

        if ($applicationStatus === 'ongoing') {
            return 'pending';
        }

        if ($votingStatus === 'upcoming') {
            return 'upcoming';
        }
        if ($applicationStatus === 'upcoming') {
            return 'upcoming';
        }
        return 'pending';
    }
}
