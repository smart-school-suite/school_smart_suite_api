<?php

namespace App\Services\Promotion;

use App\Jobs\Promotion\StudentPromotionJob;
use App\Models\Promotion\PromotionRun;
use App\Exceptions\AppException;
use App\Models\Promotion\PromotionRunLog;

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
            ->map(function ($r) {
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
                    'created_at'         => $r->created_at,
                    'updated_at'         => $r->updated_at,
                ];
            });
    }
    public function triggerPromotion(object $currentSchool, object $authUser, array $payload)
    {
        $runId = $payload['promotion_run_id'];
        $promotionRun = PromotionRun::where("school_branch_id", $currentSchool->id)
            ->where("id", $runId)
            ->first();

        if (!$promotionRun) {
            throw new AppException(
                "Promotion run not found.",
                404,
                "Promotion Run Not Found",
                "The requested promotion run does not exist or does not belong to this school branch.",
                null
            );
        }

        if (in_array($promotionRun->status, ['processing', 'queued'])) {
            throw new AppException(
                "Promotion run is already in progress.",
                409,
                "Operation Already In Progress",
                "This promotion run has already been triggered and is currently in the queue or processing.",
                null
            );
        }

        if ($promotionRun->status === 'completed') {
            throw new AppException(
                "Promotion run has already been completed.",
                400,
                "Already Completed",
                "This promotion run has already executed successfully. Use the retry action if you need to run it again.",
                null
            );
        }

        $status = $payload['status'];
        $initiateAt = $payload['initiate_at'] ?? null;

        $promotionRun->update([
            'status' => $status === 'scheduled' ? 'scheduled' : 'pending',
            'authorable_type' => get_class($authUser),
            'authorable_id' => $authUser->id,
            'started_at' =>  $status === 'scheduled' ? $initiateAt  : now(),
        ]);

        $job = StudentPromotionJob::dispatch(
            $promotionRun->id,
            $promotionRun->school_branch_id
        );

        if ($status === 'scheduled' && $initiateAt) {
            $job->delay(now()->parse($initiateAt));
        }

        return $promotionRun;
    }
    public function getPromotionRunLogs(object $currentSchool, string $runId)
    {
        $logs = PromotionRunLog::where("school_branch_id", $currentSchool->id)
            ->where("promotion_run_id", $runId)
            ->get();

        if ($logs->isEmpty()) {
            throw new AppException(
                "Promotion run logs not found.",
                404,
                "Logs Not Found",
                "No logs were found for the specified promotion run under this school branch.",
                null
            );
        }

        return $logs;
    }
    public function retryPromotionRun(object $currentSchool, string $runId)
    {
        $promotionRun = PromotionRun::where("school_branch_id", $currentSchool->id)
            ->where("id", $runId)
            ->first();

        if (!$promotionRun) {
            throw new AppException(
                "Promotion run not found.",
                404,
                "Promotion Run Not Found",
                "The promotion run you are trying to retry does not exist or does not belong to this school branch.",
                null
            );
        }

        if ($promotionRun->status === 'completed') {
            throw new AppException(
                "Cannot retry a completed promotion run.",
                400,
                "Invalid Run State",
                "This promotion run has already completed successfully and does not need to be retried.",
                null
            );
        }

        if (in_array($promotionRun->status, ['processing', 'queued'])) {
            throw new AppException(
                "Promotion run is currently processing.",
                409,
                "Job Already In Progress",
                "This promotion run is already queued or processing. Please wait for it to finish before retrying.",
                null
            );
        }

        $promotionRun->update([
            'status' => 'processing'
        ]);

        StudentPromotionJob::dispatch(
            $promotionRun->id,
            $promotionRun->school_branch_id
        );

        return $promotionRun;
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

        return $promotion;
    }
    public function cancelPromotionRun(object $currentSchool, string $runId)
    {
        $promotion = PromotionRun::where("school_branch_id", $currentSchool->id)
            ->where("id", $runId)
            ->first();

        if (!$promotion) {
            throw new AppException(
                "Promotion run not found.",
                404,
                "Promotion Run Not Found",
                "The promotion run you are trying to cancel does not exist or does not belong to this school branch.",
                null
            );
        }

        if ($promotion->status === 'completed') {
            throw new AppException(
                "Cannot cancel a completed promotion run.",
                400,
                "Operation Not Allowed",
                "This promotion run has already been completed and processed. Completed runs cannot be cancelled.",
                null
            );
        }

        if ($promotion->status === 'cancelled') {
            throw new AppException(
                "Promotion run is already cancelled.",
                409,
                "Already Cancelled",
                "This promotion run was previously cancelled. No further action is required.",
                null
            );
        }

        $promotion->update([
            "status" => "cancelled"
        ]);

        return $promotion;
    }
}
