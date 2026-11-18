<?php

namespace App\Http\Resources\ResitResult;

use App\Services\ResitResult\ResitResultService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** A resit exam on the resit results page, like ExamResultResource. */
class ResitResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $now = now();
        $status = match (true) {
            !$this->start_date || $now->lt($this->start_date) => 'upcoming',
            !$this->end_date || $now->lte($this->end_date->copy()->endOfDay()) => 'active',
            default => 'finished',
        };
        $progress = ResitResultService::progressCounts($this->resource);
        $publicationStatus = ResitResultService::publicationStatus($this->resource);

        return [
            'id' => $this->id,
            'exam_name' => $this->examType->exam_name ?? null,
            'exam_type' => $this->examType->type ?? null,
            'semester_name' => $this->examType->semesters->name ?? null,
            'specialty_name' => $this->schoolYear->specialty->specialty_name ?? null,
            'level_name' => $this->schoolYear->specialty->level->name ?? null,
            'level_number' => $this->schoolYear->specialty->level->level ?? null,
            'school_year' => $this->schoolYear->systemAcademicYear->name ?? null,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $status,
            'publication_status' => $publicationStatus,
            'is_ready_to_publish' => $publicationStatus === 'ready_to_publish',
            'published_at' => $this->resultPublication?->published_at,
            'total_candidates' => $progress['total'],
            'evaluated_candidates' => $progress['evaluated'],
            'not_evaluated_candidates' => $progress['not_evaluated'],
            'max_score' => $this->max_score,
            'is_grade_scale_configured' => (bool) $this->grades_category_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
