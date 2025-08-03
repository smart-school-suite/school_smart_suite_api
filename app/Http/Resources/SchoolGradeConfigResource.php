<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchoolGradeConfigResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
             'id'=> $this->id,
             'grade_title' => $this->gradesCategory->title,
             'isgrades_configured' => $this->isgrades_configured,
             'max_score' => $this->max_score,
             'grades_category_id' => $this->grades_category_id,
             'status' => $this->gradesCategory->status,
             'created_at' => $this->created_at,
             'updated_at' => $this->updated_at
        ];
    }
}
