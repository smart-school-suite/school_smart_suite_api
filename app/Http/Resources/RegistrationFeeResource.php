<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegistrationFeeResource extends JsonResource
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
            'status' => $this->status,
            'amount' => $this->amount,
            'title' => $this->title,
            'student_name' => $this->student->name,
            'level_name' => $this->level->name,
            'level' => $this->level->level,
            'specialty_name' => $this->specialty->specialty_name,
        ];
    }
}
