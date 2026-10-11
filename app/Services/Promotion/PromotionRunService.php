<?php

namespace App\Services\Promotion;

use App\Models\Promotion\PromotionRun;

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

    public function triggerPromotion(object $currentSchool, string $runId, array $authUser) {}
    public function getPromotionRunLogs(object $currentSchool, string $runId) {}

    public function retryPromotionRun(object $currentSchool, string $runId) {}

    public function getPromotionRunById(object $currentSchool, string $runId)
    {
        return PromotionRun::where('school_branch_id', $currentSchool->id)
            ->with([
                'schoolYear.systemAcademicYear',
                'schoolYear.specialty.level',
                'schoolYear.specialty.department',
            ])
            ->where("id", $runId)
            ->first();
    }
}
