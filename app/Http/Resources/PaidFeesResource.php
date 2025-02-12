<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaidFeesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
                "id" => $this->paidFeesData->id,
                "fee_name" => $this->paidFeesData->fee_name,
                "amount" => $this->paidFeesData->amount,
                "student_name" => $this->paidFeesData->student->name,
                "specailty_name" => $this->paidFeesData->student->specialty->specialty_name,
                "level" => $this->paidFeesData->student->level->name,
        ];
    }
}
