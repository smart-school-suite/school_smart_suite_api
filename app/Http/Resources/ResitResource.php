<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_title' => $this->courses->course_title ?? null,
            'course_code' => $this->courses->course_code ?? null,
            'course_credit' => (float) $this->courses->credit ?? 0,
            'specialty_name' => $this->exam->schoolYear->specialty->specialty_name ?? null,
            'level_name' => $this->exam->schoolYear->specialty->level->name ?? null,
            'level_number' => $this->exam->schoolYear->specialty->level->level ?? null,
            'payment_status' => $this->paid_status === 'Paid' ? 'paid' : 'unpaid',
            'student_name' => $this->student->name ?? null,
            "school_year" => $this->exam->schoolYear->systemAcademicYear->name ?? null,
            "exam_name" => $this->exam->examType->exam_name ?? null,
            "semester" => $this->exam->examType->semesters->name ?? null,
            'resit_fee' =>  (float) $this->fee ?? 0,
            "carry_over_status" => (bool) $this->is_carry_over ?? null,
            "attempts" => $this->attempts ?? 0 ,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at
        ];
    }
}
