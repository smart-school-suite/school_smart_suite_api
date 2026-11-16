<?php

namespace App\Services\TuitionFee;

use App\Models\AcademicYear\SchoolAcademicYear;
use App\Models\Student;
use App\Models\TuitionFees;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * The single place tuition fee records are generated and students are
 * assigned their school academic year. Used by student registration,
 * student promotion and academic year creation, so every path follows the
 * same rules:
 *
 * - A student's applicable academic year is the latest (by start date)
 *   academic year of their specialty, in their school branch, that has not
 *   ended yet. With none, tuition is deferred until one is created.
 * - Each academic year is its own tuition obligation: a student gets at most
 *   one tuition fee record per academic year, and a new record for each new
 *   academic year.
 * - The amount is the student's specialty tuition fee (school_fee), which is
 *   the tuition configured for that specialty's level.
 *
 * Every method is idempotent: running it again for the same student and
 * academic year returns the existing record instead of creating another,
 * which the unique (student_id, school_academic_year_id) index also enforces.
 */
class TuitionFeeGenerationService
{
    /**
     * Assigns the student's applicable academic year and bills its tuition.
     * Returns the tuition fee record, or null when the student's specialty
     * has no applicable academic year yet (tuition is deferred).
     */
    public function syncStudent(Student $student): ?TuitionFees
    {
        return DB::transaction(function () use ($student) {
            $academicYear = $this->applicableAcademicYear($student);

            $this->assignAcademicYear($student, $academicYear);

            return $academicYear ? $this->generate($student, $academicYear) : null;
        });
    }

    /**
     * Bills the student for the academic year and makes it their academic
     * year when it is their latest applicable one. Returns null when the
     * student is not eligible for the academic year.
     */
    public function generateForAcademicYear(Student $student, SchoolAcademicYear $academicYear): ?TuitionFees
    {
        if (!$this->isEligible($student, $academicYear)) {
            return null;
        }

        return DB::transaction(function () use ($student, $academicYear) {
            $tuitionFee = $this->generate($student, $academicYear);

            $this->assignAcademicYear($student, $this->applicableAcademicYear($student));

            return $tuitionFee;
        });
    }

    /** The latest academic year of the student's specialty that has not ended, if any. */
    public function applicableAcademicYear(Student $student): ?SchoolAcademicYear
    {
        return SchoolAcademicYear::where('school_branch_id', $student->school_branch_id)
            ->where('specialty_id', $student->specialty_id)
            ->whereDate('end_date', '>=', now()->toDateString())
            ->orderByDesc('start_date')
            ->orderByDesc('created_at')
            ->first();
    }

    /** Whether the academic year can bill the student: same branch and specialty, not dropped out, not ended. */
    public function isEligible(Student $student, SchoolAcademicYear $academicYear): bool
    {
        return $student->school_branch_id === $academicYear->school_branch_id
            && $student->specialty_id === $academicYear->specialty_id
            && !$student->dropout_status
            && $this->hasNotEnded($academicYear);
    }

    public function hasNotEnded(SchoolAcademicYear $academicYear): bool
    {
        return substr((string) $academicYear->end_date, 0, 10) >= now()->toDateString();
    }

    /**
     * The student's tuition fee record for the academic year, created when
     * missing. A record of the student's for the same specialty that has no
     * academic year yet (created before records were tied to academic years)
     * is linked to the academic year instead of billing the student twice.
     */
    private function generate(Student $student, SchoolAcademicYear $academicYear): TuitionFees
    {
        $existing = $this->existingFor($student, $academicYear);
        if ($existing) {
            return $existing;
        }

        $unlinked = TuitionFees::where('school_branch_id', $student->school_branch_id)
            ->where('student_id', $student->id)
            ->where('specialty_id', $academicYear->specialty_id)
            ->whereNull('school_academic_year_id')
            ->lockForUpdate()
            ->first();

        if ($unlinked) {
            $unlinked->update(['school_academic_year_id' => $academicYear->id]);
            return $unlinked;
        }

        $specialty = $academicYear->specialty()->firstOrFail();

        try {
            // A savepoint, so a concurrent insert that wins the race does not
            // abort the surrounding transaction.
            return DB::transaction(fn() => TuitionFees::create([
                'student_id' => $student->id,
                'school_branch_id' => $student->school_branch_id,
                'specialty_id' => $specialty->id,
                'school_academic_year_id' => $academicYear->id,
                'amount_paid' => 0.00,
                'tution_fee_total' => $specialty->school_fee,
            ]));
        } catch (UniqueConstraintViolationException) {
            return $this->existingFor($student, $academicYear);
        }
    }

    private function existingFor(Student $student, SchoolAcademicYear $academicYear): ?TuitionFees
    {
        return TuitionFees::where('student_id', $student->id)
            ->where('school_academic_year_id', $academicYear->id)
            ->first();
    }

    private function assignAcademicYear(Student $student, ?SchoolAcademicYear $academicYear): void
    {
        $academicYearId = $academicYear?->id;

        if ($student->school_academic_year_id !== $academicYearId) {
            $student->forceFill(['school_academic_year_id' => $academicYearId])->save();
        }
    }
}
