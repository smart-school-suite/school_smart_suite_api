<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegistrationFeeTransResource extends JsonResource
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
            'transaction_id' => $this->transaction_id,
            'payment_method' => $this->payment_method,
            'amount' => $this->amount,
            'student_name' => $this->registrationFee->student->name ?? null,
            'level_name' => $this->registrationFee->level->name ?? null,
            'level' => $this->registrationFee->level->level ?? null,
            'specialty_name' => $this->registrationFee->specialty->specialty_name ?? null,
        ];
    }
}
