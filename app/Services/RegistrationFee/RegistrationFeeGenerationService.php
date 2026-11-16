<?php

namespace App\Services\RegistrationFee;

use App\Models\RegistrationFee;
use App\Models\Specialty;
use App\Models\Student;

/**
 * Generates a student's registration fee record from the registration fee
 * configured on their specialty. Idempotent: a student already holding a
 * registration fee record for the specialty is not billed again.
 */
class RegistrationFeeGenerationService
{
    public function generate(Student $student, Specialty $specialty): RegistrationFee
    {
        return RegistrationFee::firstOrCreate(
            [
                'school_branch_id' => $student->school_branch_id,
                'student_id' => $student->id,
                'specialty_id' => $specialty->id,
            ],
            [
                'amount' => $specialty->registration_fee,
                'status' => 'not paid',
            ]
        );
    }
}
