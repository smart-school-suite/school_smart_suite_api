<?php

namespace App\Services\Import;

use App\Events\Job\JobEvent;
use App\Http\Resources\Job\SystemJobResource;
use App\Models\Job\SystemJob;
use App\Models\Schooladmin;
use App\Models\Schoolbranches;
use App\Services\Job\JobBroadCastPolicyService;
use Carbon\Carbon;
use Throwable;

/**
 * Sends "job.update" events for one system job.
 *
 * Progress events are throttled by JobBroadCastPolicyService; a forced event
 * (status change, finish) always goes out. Every event carries the whole job
 * in the SystemJobResource shape, so the app can replace what it shows.
 *
 * The event is a convenience, not the source of truth: the job row is saved
 * before each event, and a failed broadcast is reported and ignored, so the
 * app can always read the outcome from the jobs endpoints.
 */
class SystemJobBroadcaster
{
    private int $lastProgress = -1;
    private ?string $lastStatus = null;
    private ?Carbon $lastAt = null;
    private mixed $actor = null;
    private mixed $school = null;
    private bool $resolved = false;

    public function __construct(private readonly SystemJob $systemJob)
    {
    }

    public function publish(SystemJob $systemJob, bool $force = false): void
    {
        $progress = (int) ($systemJob->progress ?? 0);
        $status = (string) $systemJob->status;

        if (!$force && !app(JobBroadCastPolicyService::class)->shouldBroadcast(
            $progress,
            max($this->lastProgress, 0),
            $this->lastAt,
            $status,
            $this->lastStatus,
        )) {
            return;
        }

        $this->lastProgress = $progress;
        $this->lastStatus = $status;
        $this->lastAt = now();

        [$actor, $school] = $this->recipients();

        if (!$actor || !$school) {
            return;
        }

        try {
            $job = SystemJobResource::make($systemJob->loadMissing('category'))->resolve();

            event(new JobEvent($actor, $school, [
                'job_id' => $systemJob->id,
                'status' => $job['status'],
                'stage' => $job['stage'],
                'progress' => $job['progress_percentage'],
                'is_finished' => $job['is_finished'],
                'job' => $job,
            ]));
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /** The admin who started the job and their school branch; the channel is theirs. */
    private function recipients(): array
    {
        if (!$this->resolved) {
            $this->resolved = true;
            $this->actor = $this->systemJob->initiated_by_type === Schooladmin::class
                ? Schooladmin::find($this->systemJob->initiated_by_id)
                : null;
            $this->school = Schoolbranches::find($this->systemJob->context_id);
        }

        return [$this->actor, $this->school];
    }
}
