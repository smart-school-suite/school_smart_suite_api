<?php

namespace App\Jobs\DataCleanupJobs;

use App\Models\Elections;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Exception;
class UpdateElectionStatusJob implements ShouldQueue
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
        try {
            // Validate input
            if (empty($this->electionId) || empty($this->schoolBranchId)) {
                throw new InvalidArgumentException('Election ID and School Branch ID cannot be empty');
            }

            // Fetch election with specific columns to optimize query
            $election = Elections::where('school_branch_id', $this->schoolBranchId)
                ->select([
                    'id',
                    'school_branch_id',
                    'application_start',
                    'application_end',
                    'voting_start',
                    'voting_end'
                ])
                ->find($this->electionId);

            if (!$election) {
                Log::warning('Election not found', [
                    'election_id' => $this->electionId,
                    'school_branch_id' => $this->schoolBranchId
                ]);
                return;
            }

            $currentTime = Carbon::now();

            $applicationStatus = $this->determineApplicationStatus($election, $currentTime);
            $votingStatus = $this->determineVotingStatus($election, $currentTime);
            $overallStatus = $this->determineOverallStatus($applicationStatus, $votingStatus);

            if (
                $election->application_status !== $applicationStatus ||
                $election->voting_status !== $votingStatus ||
                $election->status !== $overallStatus
            ) {
                $election->update([
                    'application_status' => $applicationStatus,
                    'voting_status' => $votingStatus,
                    'status' => $overallStatus,
                    'updated_at' => $currentTime
                ]);

                Log::info('Election status updated', [
                    'election_id' => $this->electionId,
                    'application_status' => $applicationStatus,
                    'voting_status' => $votingStatus,
                    'overall_status' => $overallStatus
                ]);
            }
        } catch (Exception $e) {
            Log::error('Failed to update election status', [
                'election_id' => $this->electionId,
                'school_branch_id' => $this->schoolBranchId,
                'error' => $e->getMessage()
            ]);
            $this->fail($e);
        }
    }

    private function determineApplicationStatus(Elections $election, Carbon $currentTime): string
    {
        try {
            $applicationStart = Carbon::parse($election->application_start);
            $applicationEnd = Carbon::parse($election->application_end);

            if ($applicationEnd->isPast()) {
                return 'ended';
            }

            if ($applicationStart->isFuture()) {
                return 'pending';
            }

            return 'ongoing';
        } catch (Exception $e) {
            Log::error('Error determining application status', [
                'election_id' => $this->electionId,
                'error' => $e->getMessage()
            ]);
            return 'pending';
        }
    }

    private function determineVotingStatus(Elections $election, Carbon $currentTime): string
    {
        try {
            $votingStart = Carbon::parse($election->voting_start);
            $votingEnd = Carbon::parse($election->voting_end);

            if ($votingEnd->isPast()) {
                return 'ended';
            }

            if ($votingStart->isFuture()) {
                return 'pending';
            }

            return 'ongoing';
        } catch (Exception $e) {
            return 'pending';
        }
    }

    private function determineOverallStatus(string $applicationStatus, string $votingStatus): string
    {
        if ($votingStatus === 'ongoing') {
            return 'ongoing';
        }

        if ($applicationStatus === 'pending' || $applicationStatus === 'ongoing') {
            return 'upcoming';
        }

        if ($applicationStatus === 'ended' && $votingStatus === 'ended') {
            return 'finished';
        }

        return 'upcoming';
    }
}
