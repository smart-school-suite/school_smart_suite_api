<?php

namespace App\Services\RegistrationFee;

use App\Models\AcademicYear\SchoolAcademicYear;
use App\Models\RegistrationFee;
use App\Models\Specialty;
use App\Models\Student;
use App\Services\TuitionFee\TuitionFeeGenerationService;

/**
 * Generates a student's registration fee record from the registration fee
 * configured on their specialty. Idempotent: a student already holding a
 * registration fee record for the specialty is not billed again.
 *
 * The record belongs to the academic year the student is billed in: the
 * specialty's applicable academic year (as for tuition). A student admitted
 * before their specialty has one gets it when their first tuition is billed
 * (assignAcademicYear, called by TuitionFeeGenerationService).
 */
class RegistrationFeeGenerationService
{
    public function generate(Student $student, Specialty $specialty): RegistrationFee
    {
        $academicYear = $student->specialty_id === $specialty->id
            ? app(TuitionFeeGenerationService::class)->applicableAcademicYear($student)
            : null;

        $fee = RegistrationFee::firstOrCreate(
            [
                'school_branch_id' => $student->school_branch_id,
                'student_id' => $student->id,
                'specialty_id' => $specialty->id,
            ],
            [
                'amount' => $specialty->registration_fee,
                'status' => 'not paid',
                'school_academic_year_id' => $academicYear?->id,
            ]
        );

        if ($academicYear) {
            $this->assignAcademicYear($student, $academicYear);
        }

        return $fee->refresh();
    }

    /**
     * Gives the student's registration fee for the academic year's specialty
     * that academic year, when it has none yet.
     */
    public function assignAcademicYear(Student $student, SchoolAcademicYear $academicYear): void
    {
        RegistrationFee::where('school_branch_id', $student->school_branch_id)
            ->where('student_id', $student->id)
            ->where('specialty_id', $academicYear->specialty_id)
            ->whereNull('school_academic_year_id')
            ->update(['school_academic_year_id' => $academicYear->id]);
    }
}
