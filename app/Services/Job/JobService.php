<?php

namespace App\Services\Job;

use App\Groupers\SystemJob\SystemJobGrouper;
use App\Models\Job\SystemJob;
use App\Models\Job\SystemJobError;
use App\Models\Job\SystemJobEvent;
use App\Models\Job\SystemJobDetail;
use App\Exceptions\AppException;
use App\Filters\SystemJob\SystemJobFilter;
use App\Http\Resources\Job\SystemJobGroupResource;
use App\Http\Resources\Job\SystemJobResource;
use App\Services\Import\ImportErrorCollector;
use App\Services\Import\ImportErrorReason;
use Illuminate\Support\Facades\DB;
use Throwable;

class JobService
{
    /**
     * One job of the current user. The app polls this to follow a job when
     * the live event does not arrive.
     */
    public function getJobDetails(string $jobId, object $currentSchool, object $authUser): SystemJobResource
    {
        return SystemJobResource::make($this->findJob($jobId, $currentSchool, $authUser)->load('category'));
    }

    public function getJobs(array $filters, object $currentSchool, object $authUser)
    {
        $query = SystemJob::query()
            ->where('context_id', $currentSchool->id)
            ->where('initiated_by_id', $authUser->id)
            ->where('initiated_by_type', get_class($authUser))
            ->with('category')
            ->latest();

        app(SystemJobFilter::class)->apply($query, $filters);
        if (!empty($filters['group_by'])) {
            return SystemJobGroupResource::collection(
                app(SystemJobGrouper::class)->group(
                    $query,
                    $filters['group_by']
                )
            );
        }
        return SystemJobResource::collection(app(SystemJobGrouper::class)->group(
            $query,
            $filters['group_by'] ?? null
        ));
    }

    /**
     * The problems of a job, grouped by reason: each group lists the rows it
     * concerns as ranges ("2–21, 25") with one message, largest group first.
     */
    public function getJobErrors(string $jobId, object $currentSchool, object $authUser): array
    {
        $systemJob = $this->findJob($jobId, $currentSchool, $authUser);
        $result = is_array($systemJob->result) ? $systemJob->result : [];

        $groups = $result['error_groups'] ?? $this->legacyErrorGroups($jobId, $currentSchool);

        return [
            'job' => SystemJobResource::make($systemJob->load('category'))->resolve(),
            'summary' => $result['summary'] ?? $systemJob->error_message ?? $systemJob->stage,
            'error_message' => $systemJob->error_message,
            'failed_rows' => (int) ($systemJob->failed_items ?? 0),
            'group_count' => count($groups),
            'groups' => $groups,
        ];
    }

    public function deleteJob(string $jobId, object $currentSchool, object $authUser)
    {
        $job = $this->findJob($jobId, $currentSchool, $authUser);

        if (!$job->isFinished()) {
            throw new AppException(
                "Job {$jobId} is still running.",
                409,
                "Job Still Running",
                "This job has not finished yet. Wait for it to finish before deleting it.",
                '/jobs'
            );
        }

        DB::transaction(fn() => $job->delete());

        // Details and events live in MongoDB; leftovers there are harmless.
        try {
            SystemJobEvent::where("school_branch_id", $currentSchool->id)->where("job_id", $jobId)->delete();
            SystemJobDetail::where("school_branch_id", $currentSchool->id)->where("job_id", $jobId)->delete();
            SystemJobError::where("school_branch_id", $currentSchool->id)->where("job_id", $jobId)->delete();
        } catch (Throwable $exception) {
            report($exception);
        }

        return $job;
    }

    private function findJob(string $jobId, object $currentSchool, object $authUser): SystemJob
    {
        $job = SystemJob::where('context_id', $currentSchool->id)
            ->where('initiated_by_id', $authUser->id)
            ->where('id', $jobId)
            ->first();

        if (!$job) {
            throw new AppException(
                "Job not found or you do not have permission to view it.",
                404,
                "Job Not Found",
                "The job does not exist or was started by someone else.",
                '/jobs'
            );
        }

        return $job;
    }

    /**
     * Jobs from before grouped results stored one MongoDB document per row;
     * group them the same way so the app shows them alike.
     */
    private function legacyErrorGroups(string $jobId, object $currentSchool): array
    {
        try {
            $errors = SystemJobError::where('job_id', $jobId)
                ->where('school_branch_id', $currentSchool->id)
                ->get();
        } catch (Throwable $exception) {
            report($exception);
            return [];
        }

        $collector = new ImportErrorCollector();

        foreach ($errors as $error) {
            $data = (array) ($error->data ?? []);
            $row = (int) ($data['row'] ?? 0);
            $message = preg_replace('/^Row \d+:\s*/', '', (string) $error->message);
            $reason = ((int) $error->code) === 422 ? ImportErrorReason::INVALID_VALUE : ImportErrorReason::UNEXPECTED;

            foreach (preg_split('/;\s*Row \d+:\s*/', $message) as $part) {
                $collector->add($row, $reason, trim($part));
            }
        }

        return $collector->groups();
    }
}
