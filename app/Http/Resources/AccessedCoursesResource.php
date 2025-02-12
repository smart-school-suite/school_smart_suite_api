<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccessedCoursesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
                "level_id" => $this->level_id,
                "course_id" => $this->course->course->id,
                "course_name" => $this->course->course->course_title,
                "exam_id" => $this->exam_id,
                "specailty_id" => $this->course->specialty_id,
                "weighted_mark" => $this->findExam->weighted_mark,
                "student_id" => $this->findStudent->student_id
        ];
    }
}
