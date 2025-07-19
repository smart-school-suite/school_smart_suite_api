<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
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
            'student_name' => $this->name,
            'student_first_name' => $this->first_name,
            'student_last_name' => $this->last_name,
            'student_DOB' => $this->DOB,
            'student_gender' => $this->gender,
            'student_phone_one' => $this->phone_one,
            'student_phone_two' => $this->phone_two,
            'student_religion' => $this->religion,
            'student_email' => $this->email,
            'student_profile_picture' => $this->profile_picture,
            'guardian_name' => $this->guardian->name,
            'specialty_name' => $this->specialty->specialty_name,
            'level_name' => $this->level->name,
            'level_number' => $this->level->level,
            'status' => $this->status,
            'batch_title' => $this->studentBatch->name,
        ];
    }
}
