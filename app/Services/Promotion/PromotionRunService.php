<?php

namespace App\Services\Promotion;

use App\Jobs\Promotion\StudentPromotionJob;
use App\Models\Promotion\PromotionRun;
use App\Exceptions\AppException;
use App\Models\Promotion\PromotionRunLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PromotionRunService
{
    public function getPromotionRuns(object $currentSchool)
    {
        return PromotionRun::where('school_branch_id', $currentSchool->id)
            ->with([
                'schoolYear.systemAcademicYear',
                'schoolYear.specialty.level',
                'schoolYear.specialty.department',
            ])
            ->get()
            ->map(fn(PromotionRun $run) => $this->present($run));
    }

    /** A run as the list and the details drawer show it. */
    private function present(PromotionRun $r): array
    {
        $schoolYear = $r->schoolYear;
        $specialty  = $schoolYear?->specialty;
        $level      = $specialty?->level;
        $department = $specialty?->department;
        $systemYear = $schoolYear?->systemAcademicYear;

        return [
            'id'                 => $r->id,
            'ac_start_date'      => $schoolYear?->start_date,
            'ac_end_date'        => $schoolYear?->end_date,
            'academic_year'      => $systemYear?->name,
            'level'              => $level?->level,
            'level_name'         => $level?->name,
            'specialty'          => $specialty?->specialty_name,
            'department'         => $department?->department_name,
            'status'             => $r->status,
            'current_stage'      => $r->current_stage,
            'total_students'     => $r->total_students,
            'processed_students' => $r->processed_students,
            'promoted_count'     => $r->promoted_count,
            'repeated_count'     => $r->repeated_count,
            'graduated_count'    => $r->graduated_count,
            'review_count'       => $r->review_count,
            'excluded_count'     => $r->excluded_count,
            'failed_count'       => $r->failed_count,
            'started_at'         => $r->started_at,
            'completed_at'       => $r->completed_at,
            'created_at'         => $r->created_at,
            'updated_at'         => $r->updated_at,
            // What the run allows now, and why not (PromotionRunActionPolicy).
            'available_actions'  => $this->policy()->describe($r),
        ];
    }
    public function triggerPromotion(object $currentSchool, object $authUser, array $payload)
    {
        return DB::transaction(function () use ($currentSchool, $authUser, $payload) {
            $run = $this->lockedRun($currentSchool, $payload['promotion_run_id']);
            $this->assertAllowed($run, 'trigger');
            return $this->trigger($run, $authUser, $payload);
        });
    }

    /**
     * Triggers every selected run that allows it, now or at the scheduled
     * time; the others are skipped with the reason.
     */
    public function bulkTriggerPromotionRuns(object $currentSchool, object $authUser, array $payload): array
    {
        return $this->bulk($currentSchool, $payload['promotion_run_ids'], 'trigger',
            fn(PromotionRun $run) => $this->trigger($run, $authUser, $payload));
    }

    public function bulkRetryPromotionRuns(object $currentSchool, array $runIds): array
    {
        return $this->bulk($currentSchool, $runIds, 'retry', fn(PromotionRun $run) => $this->retry($run));
    }

    public function bulkCancelPromotionRuns(object $currentSchool, array $runIds): array
    {
        return $this->bulk($currentSchool, $runIds, 'cancel', fn(PromotionRun $run) => $this->cancel($run));
    }

    /**
     * The run's latest log in the current shape (see PromotionRunLogBuilder),
     * with the run's own status, so the UI can say what happened and why.
     */
    public function getPromotionRunLogs(object $currentSchool, string $runId)
    {
        $run = PromotionRun::where('school_branch_id', $currentSchool->id)->find($runId);
        if (!$run) {
            throw new AppException(
                "Promotion run not found.",
                404,
                "Promotion Run Not Found",
                "The promotion run does not exist or does not belong to this school branch.",
                null
            );
        }

        $stored = PromotionRunLog::where("school_branch_id", $currentSchool->id)
            ->where("promotion_run_id", $runId)
            ->first();

        if (!$stored) {
            throw new AppException(
                "Promotion run logs not found.",
                404,
                "No Log Yet",
                "This run has not run yet, so there is nothing to show. Trigger it to see what happens.",
                null
            );
        }

        $log = $stored->rule_logs;
        $log = is_array($log) ? $log : (json_decode(json_encode($log), true) ?? []);

        return [...PromotionRunLogBuilder::normalize($log), 'run_status' => $run->status];
    }
    public function retryPromotionRun(object $currentSchool, string $runId)
    {
        return DB::transaction(function () use ($currentSchool, $runId) {
            $run = $this->lockedRun($currentSchool, $runId);
            $this->assertAllowed($run, 'retry');
            return $this->retry($run);
        });
    }
    public function getPromotionRunById(object $currentSchool, string $runId)
    {
        $promotion = PromotionRun::where('school_branch_id', $currentSchool->id)
            ->with([
                'schoolYear.systemAcademicYear',
                'schoolYear.specialty.level',
                'schoolYear.specialty.department',
            ])
            ->where("id", $runId)
            ->first();

        if (!$promotion) {
            throw new AppException(
                "Promotion run not found.",
                404,
                "Promotion Run Not Found",
                "The requested promotion run could not be found or does not belong to the current school branch.",
                null
            );
        }

        // The relation is declared under another morph name, so resolve directly.
        $type = $promotion->authorable_type;
        $author = $type && class_exists($type) ? $type::find($promotion->authorable_id) : null;
        return $this->present($promotion) + [
            'initiated_by' => $author
                ? ($author->name ?? trim(($author->first_name ?? '') . ' ' . ($author->last_name ?? '')) ?: null)
                : null,
        ];
    }
    public function cancelPromotionRun(object $currentSchool, string $runId)
    {
        return DB::transaction(function () use ($currentSchool, $runId) {
            $run = $this->lockedRun($currentSchool, $runId);
            $this->assertAllowed($run, 'cancel');
            return $this->cancel($run);
        });
    }

    private function trigger(PromotionRun $run, object $authUser, array $payload): PromotionRun
    {
        $scheduled = ($payload['status'] ?? null) === 'scheduled' && !empty($payload['initiate_at']);
        $startAt = $scheduled ? Carbon::parse($payload['initiate_at']) : now();

        $run->update([
            // "processing" for a run queued now, so it is never confused with
            // a run that was not triggered ("pending").
            'status' => $scheduled ? 'scheduled' : 'processing',
            'authorable_type' => get_class($authUser),
            'authorable_id' => $authUser->id,
            'started_at' => $startAt,
            'completed_at' => null,
        ]);

        $this->dispatchAfterCommit($run, $scheduled ? $startAt : null);

        return $run;
    }

    private function retry(PromotionRun $run): PromotionRun
    {
        $run->update(['status' => 'processing', 'started_at' => now(), 'completed_at' => null]);
        $this->dispatchAfterCommit($run);

        return $run;
    }

    private function cancel(PromotionRun $run): PromotionRun
    {
        // The queued or scheduled job sees this and stops (StudentPromotionJob).
        $run->update(['status' => 'cancelled', 'completed_at' => now()]);

        return $run;
    }

    /**
     * Applies the action to every selected run that allows it, all in one
     * transaction; runs that don't are skipped with the reason. Refused
     * outright when no selected run allows it.
     */
    private function bulk(object $currentSchool, array $runIds, string $action, callable $apply): array
    {
        return DB::transaction(function () use ($currentSchool, $runIds, $action, $apply) {
            $runs = PromotionRun::where('school_branch_id', $currentSchool->id)
                ->whereIn('id', $runIds)
                ->with(['schoolYear.specialty.level', 'schoolYear.systemAcademicYear'])
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $done = [];
            $skipped = [];
            foreach ($runIds as $runId) {
                $run = $runs->get($runId);
                if (!$run) {
                    $skipped[] = ['id' => $runId, 'name' => null, 'reason' => 'This run no longer exists.'];
                    continue;
                }
                if ($refusal = $this->policy()->refusal($run, $action)) {
                    $skipped[] = ['id' => $run->id, 'name' => $this->runName($run), 'reason' => $refusal['description']];
                    continue;
                }
                $apply($run);
                $done[] = ['id' => $run->id, 'name' => $this->runName($run), 'status' => $run->status];
            }

            if (empty($done)) {
                $verb = ['trigger' => 'triggered', 'retry' => 'retried', 'cancel' => 'cancelled'][$action];
                throw new AppException(
                    "None of the selected promotion runs can be {$verb}.",
                    409,
                    'Nothing To ' . ucfirst($action),
                    "None of the selected promotion runs can be {$verb}. " . ($skipped[0]['reason'] ?? ''),
                    null
                );
            }

            return ['action' => $action, 'done' => $done, 'skipped' => $skipped];
        });
    }

    private function lockedRun(object $currentSchool, string $runId): PromotionRun
    {
        $run = PromotionRun::where('school_branch_id', $currentSchool->id)->lockForUpdate()->find($runId);
        if (!$run) {
            throw new AppException(
                "Promotion run not found.",
                404,
                "Promotion Run Not Found",
                "The promotion run does not exist or does not belong to this school branch.",
                null
            );
        }
        return $run;
    }

    private function assertAllowed(PromotionRun $run, string $action): void
    {
        if ($refusal = $this->policy()->refusal($run, $action)) {
            throw new AppException($refusal['description'], $refusal['code'], $refusal['title'], $refusal['description'], null);
        }
    }

    /** Queues the run's job once the status change is committed. */
    private function dispatchAfterCommit(PromotionRun $run, ?Carbon $at = null): void
    {
        $job = StudentPromotionJob::dispatch($run->id, $run->school_branch_id)->afterCommit();
        if ($at && $at->isFuture()) {
            $job->delay($at);
        }
    }

    private function runName(PromotionRun $run): string
    {
        $specialty = $run->schoolYear?->specialty;
        return trim(implode(' · ', array_filter([
            $specialty?->specialty_name,
            $specialty?->level?->name,
            $run->schoolYear?->systemAcademicYear?->name,
        ]))) ?: 'Promotion run';
    }

    private function policy(): PromotionRunActionPolicy
    {
        return app(PromotionRunActionPolicy::class);
    }
}
