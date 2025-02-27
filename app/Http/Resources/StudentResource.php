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
            'student_id' => $this->id,
            'student_name' => $this->name,
            'student_first_name' => $this->first_name,
            'student_last_name' => $this->last_name,
            'student_DOB' => $this->DOB,
            'student_gender' => $this->gender,
            'student_phone_one' => $this->phone_one,
            'student_phone_two' => $this->phone_two,
            'student_religion' => $this->religion,
            'student_payment_format' => $this->payment_format,
            'student_email' => $this->email,
            'student_profile_picture' => $this->profile_picture,
            'guardian_name' => $this->guardian->name,
            'guardian_address' => $this->guardian->address,
            'guardian_email' => $this->guardian->email,
            'guardian_phone_one' => $this->guardian->phone_one,
            'guardian_phone_two' => $this->guardian->phone_two,
            'guardian_occupation' => $this->guardian->occupation,
            'guardian_cultural_background' => $this->guardian->cultural_background,
            'guardian_preferred_contact_method' => $this->guardian->preferred_contact_method,
            'guardian_marital_status' => $this->guardian->marital_status,
            'guardian_religion' => $this->guardian->religion,
            'guardian_referral_source' => $this->guardian->referral_source,
            'guardian_preferred_language' => $this->guardian->preferred_language_of_communication,
            'guardian_school_branch_id' => $this->guardian->school_branch_id,
            'guardian_relationship_to_student' => $this->guardian->relationship_to_student,
            'specialty_title' => $this->specialty->specialty_name,
            'level_name' => $this->level->name,
            'level_number' => $this->level->level,
            'batch_title' => $this->studentBatch->name,
        ];
    }
}
