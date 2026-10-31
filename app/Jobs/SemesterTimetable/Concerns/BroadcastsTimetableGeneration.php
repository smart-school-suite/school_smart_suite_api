<?php

namespace App\Jobs\SemesterTimetable\Concerns;

use App\Events\SemesterTimetable\TimetableGenerationEvent;
use App\Exceptions\AppException;
use App\Models\Job\SystemJob;
use App\Models\SemesterTimetable\SemesterTimetable;
use App\Models\SemesterTimetable\SemesterTimetableVersion;
use Illuminate\Support\Str;
use Throwable;

trait BroadcastsTimetableGeneration
{
    protected bool $finalEventBroadcasted = false;

    protected function broadcastGeneration(SystemJob $systemJob, array $payload): void
    {
        $actor = $systemJob->initiatedBy;

        if (!$actor) {
            return;
        }

        try {
            TimetableGenerationEvent::dispatch($actor, $this->currentSchool, $payload);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    protected function broadcastCompletion(SystemJob $systemJob, object $response, string $timetableVersionId): void
    {
        $schedulerStatus = Str::lower($response->status ?? 'error');

        $semesterTimetable = SemesterTimetable::where('school_branch_id', $this->currentSchool->id)
            ->where('timetable_version_id', $timetableVersionId)
            ->latest()
            ->first();

        $hardCount = count($response->diagnostics['hard'] ?? []);
        $softCount = count($response->diagnostics['soft'] ?? []);

        [$status, $stage, $details] = match ($schedulerStatus) {
            'optimal' => [
                'completed',
                'Timetable Generated',
                'The timetable was generated successfully with no conflicts.',
            ],
            'partial' => [
                'partial',
                'Timetable Generated With Warnings',
                "The timetable was generated, but {$softCount} preference(s) could not be satisfied.",
            ],
            default => [
                'failed',
                'Timetable Generation Failed',
                "The timetable could not be generated because {$hardCount} required constraint(s) could not be satisfied.",
            ],
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
            'solution_path_count' => collect($response->suggestions ?? [])->sum(fn($paths) => count($paths)),
            'semester_timetable_id' => $semesterTimetable?->id,
            'version' => SemesterTimetableVersion::find($timetableVersionId),
        ]);
    }

    protected function broadcastFailure(SystemJob $systemJob, Throwable $exception): void
    {
        if ($this->finalEventBroadcasted) {
            return;
        }

        $this->finalEventBroadcasted = true;

        $details = $exception instanceof AppException
            ? ($exception->getDescription() ?: $exception->getMessage())
            : 'Something went wrong while generating the timetable. Please try again.';

        $versionId = $this->payload['version_id'] ?? null;

        $this->broadcastGeneration($systemJob, [
            'stage' => $exception instanceof AppException && $exception->getTitle()
                ? $exception->getTitle()
                : 'Timetable Generation Failed',
            'percentage' => 100,
            'status' => 'failed',
            'scheduler_status' => 'error',
            'details' => $details,
            'version' => $versionId ? SemesterTimetableVersion::find($versionId) : null,
        ]);
    }
}
