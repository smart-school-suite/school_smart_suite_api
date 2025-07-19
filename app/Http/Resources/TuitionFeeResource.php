<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TuitionFeeResource extends JsonResource
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
            'amount_paid' => $this->amount_paid,
            'amount_left' => $this->amount_left,
            'tution_fee_total' => $this->tution_fee_total,
            'status' => $this->status,
            'student_name' => $this->student->name,
            'level_name' => $this->student->level->name,
            'level' => $this->student->level->level,
            'specialty_name' => $this->student->specialty->specialty_name
        ];
    }
}
