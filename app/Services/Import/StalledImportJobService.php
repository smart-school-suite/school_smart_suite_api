<?php

namespace App\Services\Import;

use App\Models\Job\SystemJob;
use Illuminate\Database\Eloquent\Builder;

/**
 * An import saves its progress every few seconds while it runs. One that has
 * not done so for a long time died without reaching failed() (the worker ran
 * out of memory or the server restarted), so it is marked failed here and the
 * app stops showing it as running.
 *
 * Only imports are checked: other jobs (timetable generation) report
 * differently and are left alone.
 */
class StalledImportJobService
{
    /** A running import saves at least every few seconds, and times out after 30 minutes. */
    public const PROCESSING_STALL_MINUTES = 15;

    /** A queued import waits behind other jobs; give the queue time before giving up. */
    public const QUEUED_STALL_MINUTES = 120;

    public function failStalled(): int
    {
        $count = 0;

        $this->stalled()->each(function (SystemJob $systemJob) use (&$count) {
            $wasQueued = $systemJob->status === SystemJob::STATUS_QUEUED;
            $message = $wasQueued
                ? 'The import never started. Please import the file again.'
                : 'The import stopped responding before it finished. Rows saved before it stopped are kept; check them before importing the file again.';

            $result = is_array($systemJob->result) ? $systemJob->result : [];

            $systemJob->update([
                'status' => SystemJob::STATUS_FAILED,
                'stage' => 'Import failed',
                'progress' => 100,
                'error_code' => $wasQueued ? 'never_started' : 'stalled',
                'error_message' => $message,
                'finished_at' => now(),
                'result' => ['summary' => $message] + $result,
            ]);

            (new SystemJobBroadcaster($systemJob))->publish($systemJob, force: true);
            $count++;
        });

        return $count;
    }

    /** @return Builder<SystemJob> */
    public function stalled(): Builder
    {
        return SystemJob::query()
            ->whereIn('type', array_column(ImportType::cases(), 'value'))
            ->where(function (Builder $query) {
                $query->where(fn(Builder $q) => $q
                    ->where('status', SystemJob::STATUS_PROCESSING)
                    ->where('updated_at', '<', now()->subMinutes(self::PROCESSING_STALL_MINUTES)))
                    ->orWhere(fn(Builder $q) => $q
                        ->where('status', SystemJob::STATUS_QUEUED)
                        ->where('updated_at', '<', now()->subMinutes(self::QUEUED_STALL_MINUTES)));
            });
    }
}
