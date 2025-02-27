<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeePaymentScheduleResource extends JsonResource
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
            'num_installments' => $this->num_installments,
            'amount' => $this->amount,
            'due_date' => $this->due_date,
            'type' =>  $this->type,
            'specialty_name' => $this->specialty->specialty_name,
            'leve_name' => $this->level->level,
            'level' => $this->level->name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
