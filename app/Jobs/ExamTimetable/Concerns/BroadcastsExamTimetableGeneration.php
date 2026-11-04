<?php

namespace App\Jobs\ExamTimetable\Concerns;

use App\Events\ExamTimetable\ExamTimetableGenerationEvent;
use App\Exceptions\AppException;
use App\Models\ExamTimetable\ExamTimetableVersion;
use App\Models\Job\SystemJob;
use Illuminate\Support\Str;
use Throwable;

trait BroadcastsExamTimetableGeneration
{
    protected bool $finalEventBroadcasted = false;

    protected function broadcastGeneration(SystemJob $systemJob, array $payload): void
    {
        $actor = $systemJob->initiatedBy;

        if (!$actor) {
            return;
        }

        // Every event names its version so clients can ignore events for versions they are not displaying.
        $payload['version_id'] ??= $this->versionId ?? ($this->payload['version_id'] ?? null);

        try {
            ExamTimetableGenerationEvent::dispatch($actor, $this->currentSchool, $payload);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    protected function broadcastCompletion(SystemJob $systemJob, object $response, string $versionId): void
    {
        $schedulerStatus = Str::lower($response->status ?? 'error');
        $hardCount = count($response->diagnostics['hard'] ?? []);
        $softCount = count($response->diagnostics['soft'] ?? []);
        $unscheduled = count($response->unscheduled ?? []);

        [$status, $stage, $details] = match ($schedulerStatus) {
            'optimal' => ['completed', 'Exam Timetable Generated', 'The exam timetable was generated with every course scheduled and no conflicts.'],
            'partial' => ['partial', 'Exam Timetable Generated With Warnings', "The exam timetable was generated, but {$softCount} request(s) could not be honoured."],
            default => ['failed', 'Exam Timetable Generation Failed', "The exam timetable could not be completed: {$hardCount} required rule(s) failed" . ($unscheduled ? " and {$unscheduled} course(s) could not be scheduled." : '.')],
        };

        $this->finalEventBroadcasted = true;

        $this->broadcastGeneration($systemJob, [
            'stage' => $stage,
            'percentage' => 100,
            'status' => $status,
            'scheduler_status' => $schedulerStatus,
            'details' => $details,
            'hard_diagnostic_count' => $hardCount,
            'soft_diagnostic_count' => $softCount,
            'unscheduled_course_count' => $unscheduled,
            'solution_path_count' => collect($response->suggestions ?? [])->sum(fn($paths) => count($paths)),
            'version' => ExamTimetableVersion::find($versionId),
        ]);
    }

    protected function broadcastFailure(SystemJob $systemJob, Throwable $exception): void
    {
        if ($this->finalEventBroadcasted) {
            return;
        }

        $this->finalEventBroadcasted = true;
        $versionId = $this->versionId ?? $this->payload['version_id'] ?? null;

        $this->broadcastGeneration($systemJob, [
            'stage' => $exception instanceof AppException && $exception->getTitle() ? $exception->getTitle() : 'Exam Timetable Generation Failed',
            'percentage' => 100,
            'status' => 'failed',
            'scheduler_status' => $exception instanceof AppException ? ExamTimetableVersion::STATUS_FAILED : ExamTimetableVersion::STATUS_ERROR,
            'details' => $exception instanceof AppException
                ? ($exception->getDescription() ?: $exception->getMessage())
                : 'Something went wrong while generating the exam timetable. Please try again.',
            'version' => $versionId ? ExamTimetableVersion::find($versionId) : null,
        ]);
    }
}
