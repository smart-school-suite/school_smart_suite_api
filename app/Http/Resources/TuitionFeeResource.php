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
        $tuitionFeeTotal = (float) ($this->tuition_fee_total ?? $this->tution_fee_total ?? 0);
        $amountPaid = (float) ($this->amount_paid ?? 0);

        $amountLeft = max(0, $tuitionFeeTotal - $amountPaid);
        $status = $amountLeft <= 0 ? 'completed' : 'owing';

        return [
            'id' => $this->id,
            'amount_paid' => $amountPaid,
            'amount_left' => $amountLeft,
            'tuition_fee_total' => $tuitionFeeTotal,
            'status' => $status,

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
