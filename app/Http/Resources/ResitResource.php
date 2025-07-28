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
            'payment_status' => $this->paid_status === 'Paid' ? 'paid' : 'unpaid',
            'resit_fee' => $this->resit_fee,
            'course_name' => $this->courses->course_title,
            'specialty_name' => $this->specialty->specialty_name,
            'specialty_id' => $this->specialty->id,
            'level_name' => $this->level->name,
            'level' => $this->level->level,
            'student_name' => $this->student->name,
            'student_id' => $this->student->id
        ];
    }
}
