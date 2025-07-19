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
            'amount' => $this->amount,
            'transaction_id' => $this->transaction_id,
            'payment_method' => $this->payment_method,
            'specialty_name' => $this->tuition->specialty->specialty_name,
            'level_name' => $this->tuition->level->name,
            'level' => $this->tuition->level->level,
            'student_name' => $this->tuition->student->name
        ];
    }
}
