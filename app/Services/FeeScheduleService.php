<?php

namespace App\Services;

use App\Models\FeeSchedule;
use App\Models\Student;
use App\Models\StudentFeeSchedule;
use Illuminate\Support\Collection;

class FeeScheduleService
{

    //new methods
    public function getFeeSchedule($currentSchool){
        $feeSchedule = FeeSchedule::where("school_branch_id", $currentSchool->id)
                                   ->with(['specialty.level', 'schoolSemester.semester'])
                                   ->get();
        return $feeSchedule;
    }
    public function deleteFeeShedule($currentSchool, $feeScheduleId){
       $feeSchedule = FeeSchedule::where("school_branch_id", $currentSchool->id)
                        ->findOrFail($feeScheduleId);
       $feeSchedule->delete();
       return $feeSchedule;
    }
    public function getStudentFeeSchedule($currentSchool, string $studentId): Collection
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
            $gramification =$item->gramification;

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
}
