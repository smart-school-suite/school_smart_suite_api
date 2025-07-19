<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdditionalFeeResource extends JsonResource
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
            'status' => $this->status,
            'student_name' => $this->student->name,
            'specialty_name' => $this->specialty->specialty_name,
            'level_name' => $this->level->name,
            'level' => $this->level->level,
            'category' => $this->feeCategory->title
        ];
    }
}
