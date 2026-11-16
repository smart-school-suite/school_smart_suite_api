<?php

namespace Database\Seeders;

use App\Models\AcademicYear\SchoolAcademicYear;
use App\Models\RegistrationFee;
use App\Models\Student;
use App\Models\TuitionFees;
use App\Services\RegistrationFee\RegistrationFeeGenerationService;
use App\Services\TuitionFee\TuitionFeeGenerationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Brings existing data in line with event-driven fee generation. Run once
 * after deploying, and again whenever needed: every step is idempotent and
 * only fills in what is missing.
 *
 *   php artisan db:seed --class=SyncStudentFeesSeeder --force
 *
 * 1. Links tuition fee records created before records were tied to academic
 *    years to the academic year of their specialty that was running when
 *    they were created, when exactly one was, and the student has no record
 *    for it yet.
 * 2. Bills every academic year that has not ended to its specialty's eligible
 *    students, as creating that academic year now does.
 * 3. Gives every student without a registration fee record one for their
 *    specialty, and assigns every student their applicable academic year,
 *    billing its tuition (or clearing a year that no longer applies).
 *
 * Each student and academic year is processed in its own transaction, so a
 * failure is reported and skipped without undoing the rest.
 */
class SyncStudentFeesSeeder extends Seeder
{
    private array $counts = [
        'tuition_linked' => 0,
        'tuition_created' => 0,
        'registration_created' => 0,
        'academic_year_assigned' => 0,
        'failed' => 0,
    ];

    public function __construct(
        private TuitionFeeGenerationService $tuitionFees,
        private RegistrationFeeGenerationService $registrationFees,
    ) {}

    public function run(): void
    {
        $this->linkUnlinkedTuitionFees();
        $this->billOpenAcademicYears();
        $this->syncStudents();

        $this->report();
    }

    private function linkUnlinkedTuitionFees(): void
    {
        TuitionFees::whereNull('school_academic_year_id')->lazyById(200)->each(function (TuitionFees $fee) {
            $createdOn = $fee->created_at?->toDateString();
            if (!$createdOn) {
                return;
            }

            $years = SchoolAcademicYear::where('school_branch_id', $fee->school_branch_id)
                ->where('specialty_id', $fee->specialty_id)
                ->whereDate('start_date', '<=', $createdOn)
                ->whereDate('end_date', '>=', $createdOn)
                ->pluck('id');

            if ($years->count() !== 1) {
                return;
            }

            $taken = TuitionFees::where('student_id', $fee->student_id)
                ->where('school_academic_year_id', $years->first())
                ->exists();

            if (!$taken) {
                $fee->update(['school_academic_year_id' => $years->first()]);
                $this->counts['tuition_linked']++;
            }
        });
    }

    private function billOpenAcademicYears(): void
    {
        SchoolAcademicYear::whereDate('end_date', '>=', now()->toDateString())
            ->lazyById(100)
            ->each(function (SchoolAcademicYear $academicYear) {
                Student::where('school_branch_id', $academicYear->school_branch_id)
                    ->where('specialty_id', $academicYear->specialty_id)
                    ->where('dropout_status', false)
                    ->lazyById(200)
                    ->each(fn(Student $student) => $this->attempt(
                        "student {$student->id}, academic year {$academicYear->id}",
                        fn() => $this->countCreated($this->tuitionFees->generateForAcademicYear($student, $academicYear)),
                    ));
            });
    }

    private function syncStudents(): void
    {
        Student::with('specialty')->lazyById(200)->each(function (Student $student) {
            $this->attempt("student {$student->id}", function () use ($student) {
                DB::transaction(function () use ($student) {
                    $hasRegistrationFee = RegistrationFee::where('student_id', $student->id)->exists();
                    if (!$hasRegistrationFee && $student->specialty) {
                        $this->registrationFees->generate($student, $student->specialty);
                        $this->counts['registration_created']++;
                    }

                    if ($student->dropout_status) {
                        return;
                    }

                    $before = $student->school_academic_year_id;
                    $this->countCreated($this->tuitionFees->syncStudent($student));
                    if ($student->school_academic_year_id !== $before) {
                        $this->counts['academic_year_assigned']++;
                    }
                });
            });
        });
    }

    private function countCreated(?TuitionFees $fee): void
    {
        if ($fee?->wasRecentlyCreated) {
            $this->counts['tuition_created']++;
        }
    }

    private function attempt(string $subject, callable $work): void
    {
        try {
            $work();
        } catch (Throwable $e) {
            $this->counts['failed']++;
            report($e);
            $this->command?->error("Skipped {$subject}: {$e->getMessage()}");
        }
    }

    private function report(): void
    {
        $unlinked = TuitionFees::whereNull('school_academic_year_id')->count();

        $this->command?->table(
            ['Step', 'Count'],
            [
                ['Legacy tuition records linked to an academic year', $this->counts['tuition_linked']],
                ['Tuition records created', $this->counts['tuition_created']],
                ['Registration fee records created', $this->counts['registration_created']],
                ['Students whose academic year changed', $this->counts['academic_year_assigned']],
                ['Tuition records still without an academic year', $unlinked],
                ['Failures (see log)', $this->counts['failed']],
            ]
        );
    }
}
