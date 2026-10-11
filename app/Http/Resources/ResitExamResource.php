<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResitExamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $now = now();
        $startDate = $this->start_date;
        $endDate = $this->end_date;

        if (!$startDate && !$endDate) {
            $status = 'not_scheduled';
        } elseif ($startDate && $now->lt($startDate)) {
            $status = 'upcoming';
        } elseif ($startDate && $endDate && $now->between($startDate, $endDate)) {
            $status = 'active';
        } elseif ($endDate && $now->gt($endDate)) {
            $status = 'finished';
        } else {
            $status = 'not_scheduled';
        }

        return [
            'id' => $this->id,
            'exam_name' => $this->examType->exam_name,
            'exam_type' => $this->examType->type,
            'semester_name' => $this->examType->semesters->name ?? null,
            'specialty_name' => $this->schoolYear->specialty->specialty_name ?? null,
            'level_name' => $this->schoolYear->specialty->level->name ?? null,
            'level_number' => $this->schoolYear->specialty->level->level ?? null,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $status,
            'timetable_published' => $this->timetable_published ? 'created' : 'not created',
            'school_year' => $this->schoolYear->systemAcademicYear->name ?? null,
            'academic_year_start' => $this->schoolYear->start_date ?? null,
            'academic_year_end' => $this->schoolYear->end_date ?? null,
            'max_score' => $this->max_score,
            'is_grade_scale_configured' => $this->grades_category_id ? true : false,
            'candidates' => $this->resitCandidates->count() ?? 0,
            "created_at" => $this->created_at ?? null,
            "updated_at" => $this->updated_at ?? null
        ];
    }
}
