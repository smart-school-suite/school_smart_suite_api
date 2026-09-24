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
            'amount' =>  $this->amount ?? 0,
            'payment_method' => $this->payment_method ?? null,
            'payment_status' => $this->studentResit->payment_status ?? null,
            'resit_fee' =>  $this->studentResit->fee ?? 0,
            'student_name' => $this->studentResit->student->name ?? null,
            'specialty_name' => $this->studentResit->student->specialty->specialty_name ?? null,
            'level_name' => $this->studentResit->student->specialty->level->name ?? null,
            'level' => $this->studentResit->student->specialty->level->level ?? null,
            'created_at' => $this->created_at ?? null,
            'updated_at' => $this->updated_at ?? null
        ];
    }
}
