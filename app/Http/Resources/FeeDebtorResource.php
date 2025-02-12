<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeeDebtorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
               'id' => $this->feeDebtors->id,
              'specialty_name' => $this->feeDebtors->specialty->specialty_name,
              'level' => $this->feeDebtors->level->name,
              'student_name' => $this->feeDebtors->name,
              'total_fee_debt' => $this->feeDebtors->total_fee_debt,
              'fee_status' => $this->feeDebtors->fee_status,
        ];
    }
}
