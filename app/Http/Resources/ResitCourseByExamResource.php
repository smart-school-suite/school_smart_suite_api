<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResitCourseByExamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'resit_exam_id' => $this->exam_id,
            'course_id' => $this->course_id,
            'course_name' => $this->courses->course_title,
        ];
    }
}
