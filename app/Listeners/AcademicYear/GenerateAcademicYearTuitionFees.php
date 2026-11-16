<?php

namespace App\Listeners\AcademicYear;

use App\Events\AcademicYear\SchoolAcademicYearCreated;
use App\Models\Student;
use App\Services\TuitionFee\TuitionFeeGenerationService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Bills every eligible student of the new academic year's specialty for it,
 * and makes it their academic year when it is their latest applicable one.
 *
 * Queued once the academic year is committed. Each student is billed in its
 * own transaction and billing is idempotent, so a retried or repeated run
 * only fills in the students not billed yet.
 */
class GenerateAcademicYearTuitionFees implements ShouldQueue, ShouldHandleEventsAfterCommit
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function __construct(private TuitionFeeGenerationService $tuitionFees) {}

    public function handle(SchoolAcademicYearCreated $event): void
    {
        $academicYear = $event->academicYear;

        if (!$this->tuitionFees->hasNotEnded($academicYear)) {
            return;
        }

        Student::where('school_branch_id', $academicYear->school_branch_id)
            ->where('specialty_id', $academicYear->specialty_id)
            ->where('dropout_status', false)
            ->lazyById(200)
            ->each(fn(Student $student) => $this->tuitionFees->generateForAcademicYear($student, $academicYear));
    }
}
