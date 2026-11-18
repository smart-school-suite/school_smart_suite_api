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
        // Paid amounts are derived from the tuition fee transactions.
        return [
            'id' => $this->id,
            'amount_paid' => $this->amount_paid,
            'amount_left' => $this->amount_left,
            'tuition_fee_total' => (float) $this->tution_fee_total,
            'status' => $this->payment_status,

            // Nested relationships using optional() or null-safe operators
            'name' => $this->student?->name,
            'username' => $this->student?->username,
            'first_name' => $this->student?->first_name,
            'last_name' => $this->student?->last_name,
            'profile_picture' => $this->student?->profile_picture,

            'specialty_name' => $this->specialty?->specialty_name,
            'department' => $this->specialty?->department?->department_name,
            'level_name' => $this->specialty?->level?->name,
            'level_number' => $this->specialty?->level?->level,

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
