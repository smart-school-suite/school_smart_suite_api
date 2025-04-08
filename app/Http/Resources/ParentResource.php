<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParentResource extends JsonResource
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
            'guardian_name'=> $this->name,
            'address' => $this->address,
            'email' => $this->email,
            'phone_one' => $this->phone_one,
            'phone_two' => $this->phone_two,
            'occupation' => $this->occupation,
            'cultural_background' => $this->cultural_background,
            'preferred_contact_method' => $this->preferred_contact_method,
            'relationship_to_student' => $this->relationship_to_student
        ];
    }
}
