<?php

namespace App\Services\TuitionFee;

use App\Models\FeeSchedule;
use App\Models\Student;
use App\Models\TuitionFees;
use Illuminate\Support\Collection;
use App\Exceptions\AppException;
use Exception;
use App\Models\Installment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use App\Events\Actions\AdminActionEvent;
use App\Events\Actions\StudentActionEvent;

class TuitionFeeScheduleService
{
    public function autoCreateFeePaymentSchedule(object $currentSchool, array $data)
    {
        try {
            $feeSchedule = FeeSchedule::where("school_branch_id", $currentSchool->id)
                ->with(['specialty.level', 'schoolSemester.semester'])
                ->findOrFail($data['fee_schedule_id']);

            $installments = Installment::whereIn('count', range(1, $data['installments']))
                ->orderBy('count')
                ->get();

            if ($installments->count() < $data['installments']) {
                throw new AppException(
                    "Missing installment records.",
                    404,
                    "Configuration Error",
                    "The system is missing pre-configured installment records for the requested number of installments. Please contact an administrator.",
                    null
                );
            }

            $semesterCount = $feeSchedule->schoolSemester->semester->count;
            try {
                $startDate = Carbon::parse($feeSchedule->schoolSemester->start_date);
                $endDate = Carbon::parse($feeSchedule->schoolSemester->end_date);
            } catch (Exception $e) {
                throw new AppException(
                    "Invalid date format.",
                    400,
                    "Date Parsing Error",
                    "The semester start_date or end_date is in an invalid format.",
                    null
                );
            }

            $adjustedEndDate = $endDate->copy()->subDays(7);

            if ($adjustedEndDate->lessThan($startDate)) {
                throw new AppException(
                    "Invalid semester dates.",
                    400,
                    "Date Error",
                    "The semester duration is too short to create a valid payment schedule after applying the 7-day buffer.",
                    null
                );
            }

            $feePercentage = $this->getDecimalPercentage($data['percentage']);
            $totalFee = $feeSchedule->specialty->school_fee * $feePercentage;

            $firstSemesterAmount = $semesterCount === 1 ? $totalFee * 0.7 : $totalFee * 0.3;
            $semesterAmount = ($semesterCount === 1) ? $firstSemesterAmount : ($totalFee - $firstSemesterAmount);

            $firstInstallmentAmount = $semesterAmount * 0.6;
            $remainingAmount = $semesterAmount - $firstInstallmentAmount;
            $installmentCount = $data['installments'];
            $otherInstallmentAmount = $installmentCount > 1 ? $remainingAmount / ($installmentCount - 1) : 0;

            $installmentInterval = $installmentCount > 0 ? $startDate->diffInDays($adjustedEndDate) / $installmentCount : 0;

            $paymentSchedules = [];

            foreach ($installments as $index => $installment) {
                $amount = ($installment->count === 1) ? $firstInstallmentAmount : $otherInstallmentAmount;
                $feePercentage = ($amount / $totalFee) * 100;

                $dueDate = $startDate->copy()->addDays($installmentInterval * $index);
                $dueDate = $dueDate->greaterThan($adjustedEndDate) ? $adjustedEndDate : $dueDate;

                $paymentSchedules[] = [
                    'fee_schedule_id' => $feeSchedule->id,
                    'installment_id' => $installment->id,
                    'installment_name' => $installment->name,
                    'fee_percentage' => round($feePercentage, 2),
                    'amount' => round($amount, 2),
                    'due_date' => $dueDate->toDateString(),
                ];
            }

            return $paymentSchedules;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Fee schedule not found.",
                404,
                "Record Not Found",
                "The fee schedule with the provided ID does not exist or is not associated with your school.",
                null
            );
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred during the creation of the payment schedule.",
                500,
                "Internal Server Error",
                "A server-side issue prevented the payment schedule from being created: " . $e->getMessage(),
                null
            );
        }
    }

    private function getDecimalPercentage(float|int $number): float
    {
        $decimalValue = $number / 100;
        return round($decimalValue, 2);
    }

    public function getFeeSchedule(object $currentSchool)
    {
        try {
            $feeSchedule = FeeSchedule::where("school_branch_id", $currentSchool->id)
                ->with([
                    'schoolYear.systemAcademicYear',
                    'feeScheduleSlot',
                    'schoolYear.specialty.department',
                    'schoolYear.specialty.level'
                ])
                ->get();

            if ($feeSchedule->isEmpty()) {
                throw new AppException(
                    "No fee schedules were found for this school branch.",
                    404,
                    "No Fee Schedules Found",
                    "There are no fee schedules available in the system for your school branch. Please ensure they are properly configured.",
                    null
                );
            }

            return $feeSchedule;
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while retrieving fee schedules.",
                500,
                "Internal Server Error",
                "A server-side issue prevented the list of fee schedules from being retrieved successfully.",
                null
            );
        }
    }
    public function deleteFeeShedule(object $currentSchool, string $feeScheduleId, object $authAdmin)
    {
        $feeSchedule = FeeSchedule::where("school_branch_id", $currentSchool->id)
            ->findOrFail($feeScheduleId);
        $feeSchedule->delete();
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.feeSchedule.delete"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "tuitionFeeScheduleManagement",
                "authAdmin" => $authAdmin,
                "data" => $feeSchedule,
                "message" => "Tuition Fee Schedule Deleted",
            ]
        );
        StudentActionEvent::dispatch([
            'schoolBranch' => $currentSchool->id,
            'specialtyIds'   => [$feeSchedule->specialty_id],
            'feature'      => 'tuitionFeeScheduleDelete',
            'message'      => 'Tuition Fee Schedule Deleted',
            'data'         => $feeSchedule,
        ]);
        return $feeSchedule;
    }
    /**
     * The installments of the student's current tuition fee: the one for
     * their school academic year, else their latest one.
     */
    public function getFeeScheduleStudentId(object $currentSchool, string $studentId): Collection
    {
        $student = Student::where("school_branch_id", $currentSchool->id)
            ->findOrFail($studentId);

        $tuitionFee = TuitionFees::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $student->id)
            ->orderByRaw("school_academic_year_id = ? desc", [$student->school_academic_year_id ?? ''])
            ->orderByDesc("created_at")
            ->first();

        if (!$tuitionFee) {
            return collect();
        }

        return collect(app(TuitionFeeProgressService::class)->forTuitionFee($tuitionFee)['installments'])
            ->map(fn(array $slot) => [...$slot, 'amount' => $slot['expected_amount']]);
    }

    /** The student's fee schedule progress for every level they have been billed tuition at. */
    public function getStudentFeeSchedule(object $currentSchool, object $student): array
    {
        return $this->studentFeeScheduleByLevel($currentSchool, $student);
    }

    public function getStudentFeeScheduleLevelId(object $currentSchool, object $student, string $levelId): array
    {
        return $this->studentFeeScheduleByLevel($currentSchool, $student, $levelId);
    }

    /**
     * The student's tuition fees with their derived installment progress,
     * grouped by the level billed, lowest level first. Within a level, each
     * academic year's installments follow the previous year's.
     */
    private function studentFeeScheduleByLevel(object $currentSchool, object $student, ?string $levelId = null): array
    {
        $tuitionFees = TuitionFees::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $student->id)
            ->when($levelId, fn($query) => $query->whereHas("specialty", fn($q) => $q->where("level_id", $levelId)))
            ->with(["specialty.level", "schoolAcademicYear.systemAcademicYear"])
            ->get()
            ->filter(fn(TuitionFees $fee) => $fee->specialty?->level !== null)
            ->sortBy(fn(TuitionFees $fee) => [(int) $fee->specialty->level->level, (string) $fee->schoolAcademicYear?->start_date])
            ->values();

        $progress = app(TuitionFeeProgressService::class)->forTuitionFees($tuitionFees);

        return $tuitionFees
            ->groupBy(fn(TuitionFees $fee) => $fee->specialty->level_id)
            ->map(function (Collection $fees) use ($progress) {
                $level = $fees->first()->specialty->level;

                return [
                    'level_id' => $level->id,
                    'level_name' => "Level {$level->level} - {$level->name}",
                    'tuition_fees' => $fees->map(fn(TuitionFees $fee) => [
                        ...collect($progress[$fee->id])->except('installments')->all(),
                        'academic_year' => $fee->schoolAcademicYear?->systemAcademicYear?->name,
                    ])->values()->all(),
                    'installment_slots' => $fees
                        ->flatMap(fn(TuitionFees $fee) => collect($progress[$fee->id]['installments'])
                            ->map(fn(array $slot) => [
                                ...$slot,
                                'tuition_fee_id' => $fee->id,
                                'school_academic_year_id' => $fee->school_academic_year_id,
                            ]))
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }
}
