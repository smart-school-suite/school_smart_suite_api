<?php

namespace App\Services;

use App\Models\FeeSchedule;
use App\Models\Student;
use App\Models\StudentFeeSchedule;
use Illuminate\Support\Collection;
use App\Exceptions\AppException;
use Exception;
use App\Models\Installment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;

class FeeScheduleService
{


    public function autoCreateFeePaymentSchedule($currentSchool, $data)
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

    public function getFeeSchedule($currentSchool)
    {
        try {
            $feeSchedule = FeeSchedule::where("school_branch_id", $currentSchool->id)
                ->with(['specialty.level', 'schoolSemester.semester'])
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
    public function deleteFeeShedule($currentSchool, $feeScheduleId)
    {
        $feeSchedule = FeeSchedule::where("school_branch_id", $currentSchool->id)
            ->findOrFail($feeScheduleId);
        $feeSchedule->delete();
        return $feeSchedule;
    }
    public function getFeeScheduleStudentId($currentSchool, string $studentId): Collection
    {
        $student = Student::where("school_branch_id", $currentSchool->id)
            ->findOrFail($studentId);

        $schedule = StudentFeeSchedule::where("school_branch_id", $currentSchool->id)
            ->where("specialty_id", $student->specialty_id)
            ->where("level_id", $student->level_id)
            ->with(['student', 'feeScheduleSlot.installment', 'level', 'specialty'])
            ->get();

        $sortedSchedule = $schedule->sortBy(function ($item) {
            return $item->feeScheduleSlot->installment->count ?? PHP_INT_MAX;
        })->values();

        $formattedSchedule = $sortedSchedule->map(function ($item) {
            $installmentName = $item->feeScheduleSlot->installment->name ?? null;
            $dueDate = $item->feeScheduleSlot->due_date ?? null;
            $status = $item->status;
            $amount = $item->expected_amount;
            $gramification = $item->gramification;

            return [
                'installment' => $installmentName,
                'amount' => $amount,
                'due_date' => $dueDate,
                'status' => $status,
                'gramification' => $gramification,
            ];
        });

        return $formattedSchedule;
    }
    public function getStudentFeeSchedule($currentSchool, $student): array
    {
        $studentFeeSchedule = StudentFeeSchedule::select('student_fee_schedules.*')
            ->join('levels', 'levels.id', '=', 'student_fee_schedules.level_id')

            ->where("student_fee_schedules.school_branch_id", $currentSchool->id)
            ->where("student_fee_schedules.student_id", $student->id)

            ->orderBy('levels.level', 'asc')

            ->with(['specialty', 'level', 'feeScheduleSlot.installment'])

            ->get();

        $groupedByLevel = $studentFeeSchedule->groupBy('level_id');


        $formattedOutput = $groupedByLevel->map(function ($feeSlots, $levelId) {

            $level = $feeSlots->first()->level;

            return [
                'level_id' => $level->id,
                'level_name' => "Level {$level->level} - {$level->name}",

                'installment_slots' => $feeSlots->map(function ($slot) {
                    $feeScheduleSlot = $slot->feeScheduleSlot;
                    $installment = $feeScheduleSlot->installment;

                    return [
                        'slot_id' => $slot->id,
                        'status' => $slot->status,
                        'gramification' => $slot->gramification,
                        'installment' => optional($installment)->name,
                        'installment_id' => optional($installment)->id,
                        'amount_paid' => (float) $slot->amount_paid,
                        'amount_left' => (float) $slot->amount_left,
                        'expected_amount' => (float) $slot->expected_amount,
                        'percentage_paid' => (float) round($slot->percentage_paid, 2),
                        'due_date' => $feeScheduleSlot->due_date ? : null,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        return $formattedOutput;
    }
    public function getStudentFeeScheduleLevelId($currentSchool, $student, $levelId): array
    {
        $studentFeeSchedule = StudentFeeSchedule::select('student_fee_schedules.*')
            ->join('levels', 'levels.id', '=', 'student_fee_schedules.level_id')

            ->where("student_fee_schedules.school_branch_id", $currentSchool->id)
            ->where("student_fee_schedules.student_id", $student->id)

            ->where("student_fee_schedules.level_id", $levelId)

            ->orderBy('levels.level', 'asc')

            ->with(['specialty', 'level', 'feeScheduleSlot.installment'])

            ->get();

        if ($studentFeeSchedule->isEmpty()) {
            return [];
        }

        $groupedByLevel = $studentFeeSchedule->groupBy('level_id');

        $formattedOutput = $groupedByLevel->map(function ($feeSlots, $levelId) {

            $level = $feeSlots->first()->level;

            return [
                'level_id' => $level->id,
                'level_name' => "Level {$level->level} - {$level->name}",

                'installment_slots' => $feeSlots->map(function ($slot) {
                    $feeScheduleSlot = $slot->feeScheduleSlot;
                    $installment = optional($feeScheduleSlot)->installment;

                    return [
                        'slot_id' => $slot->id,
                        'status' => $slot->status,
                        'gramification' => $slot->gramification,
                        'installment' => optional($installment)->name,
                        'installment_id' => optional($installment)->id,
                        'amount_paid' => (float) $slot->amount_paid,
                        'amount_left' => (float) $slot->amount_left,
                        'expected_amount' => (float) $slot->expected_amount,
                        'percentage_paid' => (float) round($slot->percentage_paid, 2),
                        'due_date' => optional($feeScheduleSlot)->due_date ? optional($feeScheduleSlot)->due_date->format('d-m-Y') : null,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        return $formattedOutput;
    }
}
