<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResitTransResource extends JsonResource
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
            'transaction_id' => $this->transaction_id ?? null,
            'course_title' => $this->studentResit->courses->course_title ?? null,
            'amount' => $this->amount ?? null,
            'payment_method' => $this->payment_method ?? null,
            'paid_status' => $this->studentResit->paid_status ?? null,
            'resit_fee' => $this->studentResit->resit_fee ?? null,
            'student_name' => $this->studentResit->student->name ?? null,
            'specialty_name' => $this->studentResit->specialty->specialty_name ?? null,
            'level_name' => $this->studentResit->level->name ?? null,
            'level' => $this->studentResit->level->level?? null
        ];
    }
}
