<?php

namespace App\Services\Promotion;

use App\Models\Promotion\PromotionRun;

/**
 * Which actions a promotion run allows in its current status, and why not.
 * The single and bulk trigger / retry / cancel endpoints enforce it, and the
 * run list returns it per run so the UI offers only allowed actions.
 *
 * Statuses:
 *  - pending: never triggered.
 *  - scheduled: triggered for a later time; its job is waiting.
 *  - evaluating / processing: its job is queued or running.
 *  - completed, completed_with_issues, failed, cancelled: stopped.
 *
 * - Trigger: a run that has not run yet (pending) or was cancelled.
 * - Retry: a run that stopped without completing cleanly (failed,
 *   completed_with_issues) or was cancelled. Re-running is safe: each
 *   student's promotion record is updated in place, not duplicated.
 * - Cancel: a scheduled, queued or running run. The job checks for
 *   cancellation before it starts and while it processes students.
 */
class PromotionRunActionPolicy
{
    public const ACTIONS = ['trigger', 'retry', 'cancel'];

    /** Statuses whose job is waiting, queued or running. */
    public const IN_PROGRESS = ['scheduled', 'evaluating', 'processing'];

    /** Null when the action is allowed, else ['title', 'description', 'code']. */
    public function refusal(PromotionRun $run, string $action): ?array
    {
        $status = $run->status;

        return match ($action) {
            'trigger' => match (true) {
                in_array($status, ['pending', 'cancelled'], true) => null,
                $status === 'scheduled' => $this->deny('Already Scheduled', 'This run is already scheduled. Cancel it first to trigger it again.'),
                in_array($status, self::IN_PROGRESS, true) => $this->deny('Already Running', 'This run is already queued or processing.'),
                $status === 'completed' => $this->deny('Already Completed', 'This run has already completed.'),
                default => $this->deny('Use Retry', 'This run has already run. Use Retry to run it again.'),
            },
            'retry' => match (true) {
                in_array($status, ['failed', 'completed_with_issues', 'cancelled'], true) => null,
                $status === 'pending' => $this->deny('Not Run Yet', 'This run has not run yet. Trigger it instead.'),
                in_array($status, self::IN_PROGRESS, true) => $this->deny('Already Running', 'This run is already scheduled, queued or processing.'),
                $status === 'completed' => $this->deny('Already Completed', 'This run completed successfully and does not need to run again.'),
                default => $this->deny('Cannot Retry', "A run that is {$status} cannot be retried."),
            },
            'cancel' => match (true) {
                in_array($status, self::IN_PROGRESS, true) => null,
                $status === 'pending' => $this->deny('Nothing To Cancel', 'This run has not been triggered, so there is nothing to cancel.'),
                $status === 'cancelled' => $this->deny('Already Cancelled', 'This run is already cancelled.'),
                default => $this->deny('Already Finished', 'This run has already stopped, so it cannot be cancelled.'),
            },
            default => $this->deny('Unknown Action', "Unknown promotion run action '{$action}'.", 422),
        };
    }

    /** Every action with whether it is allowed and why not, for the run list. */
    public function describe(PromotionRun $run): array
    {
        return collect(self::ACTIONS)->mapWithKeys(function (string $action) use ($run) {
            $refusal = $this->refusal($run, $action);
            return [$action => ['allowed' => $refusal === null, 'reason' => $refusal['description'] ?? null]];
        })->all();
    }

    private function deny(string $title, string $description, int $code = 409): array
    {
        return ['title' => $title, 'description' => $description, 'code' => $code];
    }
}
