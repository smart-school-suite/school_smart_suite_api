<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TuitionFeeTransacResource extends JsonResource
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
            'amount' => $this->amount ?? null,
            'transaction_id' => $this->transaction_id ?? null,
            'payment_method' => $this->payment_method ?? null,
            'specialty_name' => $this->tuition->specialty->specialty_name ?? null,
            'department_name' => $this->tuition->specialty->department->department_name ?? null,
            'tuition_fee' => $this->tuition,
            'level_name' => $this->tuition->specialty->level->name ?? null,
            'level_number' => $this->tuition->specialty->level->level ?? null,
            'student_name' => $this->tuition->student->name ?? null,
            'created_at' => $this->created_at ?? null,
            'updated_at' => $this->updated_at ?? null
        ];
    }
}
