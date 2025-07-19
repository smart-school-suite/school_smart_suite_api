<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdditionalFeeTransactionResource extends JsonResource
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
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'specialty_name' => $this->additionFee->student->specialty->specialty_name,
            'student_name' => $this->additionFee->student->name,
            'level_name' => $this->additionFee->student->specialty->level->name,
            'category' => $this->additionFee->feeCategory->title
        ];
    }
}
