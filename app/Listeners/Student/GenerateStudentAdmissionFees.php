<?php

namespace App\Listeners\Student;

use App\Events\Student\StudentRegistered;
use App\Services\RegistrationFee\RegistrationFeeGenerationService;
use App\Services\TuitionFee\TuitionFeeGenerationService;
use Illuminate\Support\Facades\DB;

/**
 * Bills a newly registered student: their registration fee always, and
 * their tuition when their specialty already has an applicable academic
 * year, which also becomes the student's academic year. Without one,
 * tuition is deferred until the academic year is created.
 *
 * Runs synchronously so it shares the registering transaction.
 */
class GenerateStudentAdmissionFees
{
    public function __construct(
        private RegistrationFeeGenerationService $registrationFees,
        private TuitionFeeGenerationService $tuitionFees,
    ) {}

    public function handle(StudentRegistered $event): void
    {
        $student = $event->student;

        DB::transaction(function () use ($student) {
            $this->registrationFees->generate($student, $student->specialty()->firstOrFail());
            $this->tuitionFees->syncStudent($student);
        });
    }
}
