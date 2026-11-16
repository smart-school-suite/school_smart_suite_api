<?php

namespace App\Services\TuitionFee;

use App\Events\Actions\AdminActionEvent;
use App\Events\Actions\StudentActionEvent;
use App\Exceptions\AppException;
use App\Jobs\NotificationJobs\SendAdminFeeScheduleNotificationJob;
use App\Jobs\NotificationJobs\SendFeeScheduleNotificationJob;
use App\Models\FeeSchedule;
use App\Models\FeeScheduleSlot;
use App\Models\Installment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Manages an academic year's fee schedule installments (slots). A schedule is
 * saved as a whole: its installments must use distinct installment plans,
 * fall within the academic year in installment order, and together cover
 * 100% of the tuition. Amounts are not stored; each student's amount is the
 * percentage of their own tuition (see TuitionFeeProgressService).
 */
class TuitionFeeScheduleSlotService
{
    private const TOTAL_PERCENTAGE = 100.0;
    private const TOLERANCE = 0.01;

    /** Configures a schedule that has no installments yet. */
    public function createFeeScheduleSlots(object $currentSchool, string $feeScheduleId, array $slotsData, $authAdmin): bool
    {
        $feeSchedule = $this->feeSchedule($currentSchool, $feeScheduleId);

        if ($feeSchedule->feeScheduleSlot()->exists()) {
            throw new AppException(
                "This fee schedule is already configured.",
                409,
                "Schedule Already Configured",
                "This fee schedule already has installments. Edit the schedule instead of creating it again.",
                null
            );
        }

        $slots = $this->validatedSlots($feeSchedule, $slotsData);

        DB::transaction(function () use ($feeSchedule, $slots, $currentSchool) {
            foreach ($slots as $slot) {
                FeeScheduleSlot::create([
                    'due_date' => $slot['due_date'],
                    'percentage' => $slot['percentage'],
                    'installment_id' => $slot['installment_id'],
                    'fee_schedule_id' => $feeSchedule->id,
                    'school_branch_id' => $currentSchool->id,
                ]);
            }
        });

        $specialty = $feeSchedule->schoolYear->specialty;
        $scheduleData = [
            'schoolYear' => $feeSchedule->schoolYear->systemAcademicYear?->name,
            'specialty' => $specialty->specialty_name,
            'level' => $specialty->level?->level,
            'semester' => null,
        ];
        SendAdminFeeScheduleNotificationJob::dispatch($currentSchool->id, $scheduleData);
        SendFeeScheduleNotificationJob::dispatch($currentSchool->id, $specialty->id, $scheduleData);

        $this->dispatchActionEvents($currentSchool, $feeSchedule, $authAdmin, 'created', 'schoolAdmin.feeScheduleSlots.create', 'tuitionFeeScheduleCreate');

        return true;
    }

    /**
     * Replaces the schedule's installments with the given set: installments
     * sent with a slot_id are updated, new ones are created and the ones left
     * out are removed. Student progress is derived, so nothing else changes.
     */
    public function updateFeeScheduleSlots(object $currentSchool, string $feeScheduleId, array $slotsData, $authAdmin): bool
    {
        $feeSchedule = $this->feeSchedule($currentSchool, $feeScheduleId);
        $slots = $this->validatedSlots($feeSchedule, $slotsData);
        $existing = $feeSchedule->feeScheduleSlot()->get()->keyBy('id');

        $unknown = $slots->pluck('slot_id')->filter()->reject(fn($id) => $existing->has($id));
        if ($unknown->isNotEmpty()) {
            throw new AppException(
                "Some installments do not belong to this fee schedule.",
                422,
                "Unknown Installment",
                "One or more installments were removed or belong to another schedule. Reload the schedule and try again.",
                null
            );
        }

        DB::transaction(function () use ($feeSchedule, $slots, $existing, $currentSchool) {
            $kept = $slots->pluck('slot_id')->filter();
            FeeScheduleSlot::where('fee_schedule_id', $feeSchedule->id)
                ->whereNotIn('id', $kept)
                ->delete();

            foreach ($slots as $slot) {
                $values = [
                    'due_date' => $slot['due_date'],
                    'percentage' => $slot['percentage'],
                    'installment_id' => $slot['installment_id'],
                ];

                if ($slot['slot_id']) {
                    $existing[$slot['slot_id']]->update($values);
                } else {
                    FeeScheduleSlot::create([
                        ...$values,
                        'fee_schedule_id' => $feeSchedule->id,
                        'school_branch_id' => $currentSchool->id,
                    ]);
                }
            }
        });

        $this->dispatchActionEvents($currentSchool, $feeSchedule, $authAdmin, 'updated', 'schoolAdmin.feeScheduleSlot.update', 'tuitionFeeScheduleUpdate');

        return true;
    }

    /**
     * The schedule's installments in due date order. Each carries the amount
     * due for the specialty's tuition; a student's own amounts follow their
     * tuition fee total (see TuitionFeeProgressService).
     */
    public function getFeeScheduleSlots($feeScheduleId, $currentSchool)
    {
        $feeSchedule = $this->feeSchedule($currentSchool, $feeScheduleId);
        $tuition = (float) $feeSchedule->schoolYear->specialty->school_fee;

        return FeeScheduleSlot::where("school_branch_id", $currentSchool->id)
            ->where("fee_schedule_id", $feeSchedule->id)
            ->with(['installment'])
            ->orderBy('due_date')
            ->get()
            ->map(fn(FeeScheduleSlot $slot) => [
                ...$slot->toArray(),
                'due_date' => $slot->due_date?->toDateString(),
                'fee_percentage' => (float) $slot->percentage,
                'amount' => round($tuition * ((float) $slot->percentage / 100), 2),
            ]);
    }

    /**
     * Normalises and checks a whole schedule. Returns the installments in
     * installment order as [slot_id, installment_id, due_date, percentage].
     */
    private function validatedSlots(FeeSchedule $feeSchedule, array $slotsData): Collection
    {
        $installments = Installment::whereIn('id', collect($slotsData)->pluck('installment_id')->filter())
            ->get()
            ->keyBy('id');

        $slots = collect($slotsData)
            ->map(fn(array $slot) => [
                'slot_id' => $slot['slot_id'] ?? null,
                'installment_id' => $slot['installment_id'] ?? null,
                'due_date' => substr((string) ($slot['due_date'] ?? ''), 0, 10),
                'percentage' => round((float) ($slot['fee_percentage'] ?? 0), 2),
            ])
            ->sortBy(fn(array $slot) => $installments[$slot['installment_id']]->count ?? PHP_INT_MAX)
            ->values();

        $fail = fn(string $title, string $description) => throw new AppException($description, 422, $title, $description, null);

        if ($slots->isEmpty()) {
            $fail("No Installments", "Add at least one installment to the schedule.");
        }

        if ($slots->pluck('installment_id')->unique()->count() !== $slots->count()) {
            $fail("Duplicate Installment", "Each installment can only appear once in a schedule.");
        }

        if ($slots->contains(fn(array $slot) => $slot['percentage'] <= 0)) {
            $fail("Invalid Percentage", "Every installment must be a share of the tuition greater than 0%.");
        }

        $total = round($slots->sum('percentage'), 2);
        if (abs($total - self::TOTAL_PERCENTAGE) > self::TOLERANCE) {
            $fail("Percentages Must Total 100%", "The installments add up to {$total}% of the tuition. Adjust them so they total exactly 100%.");
        }

        $start = substr((string) $feeSchedule->schoolYear->start_date, 0, 10);
        $end = substr((string) $feeSchedule->schoolYear->end_date, 0, 10);
        if ($slots->contains(fn(array $slot) => $slot['due_date'] < $start || $slot['due_date'] > $end)) {
            $fail("Due Date Outside Academic Year", "Every due date must fall within the academic year, from {$start} to {$end}.");
        }

        $dates = $slots->pluck('due_date')->all();
        for ($i = 1; $i < count($dates); $i++) {
            if ($dates[$i] <= $dates[$i - 1]) {
                $fail("Due Dates Out Of Order", "Each installment must be due after the one before it.");
            }
        }

        return $slots;
    }

    private function dispatchActionEvents(object $currentSchool, FeeSchedule $feeSchedule, $authAdmin, string $action, string $permission, string $studentFeature): void
    {
        $label = ucfirst($action);

        AdminActionEvent::dispatch([
            "permissions" => [$permission],
            "roles" => ["schoolSuperAdmin", "schoolAdmin"],
            "schoolBranch" => $currentSchool->id,
            "feature" => "tuitionFeeSheduleSlotMangement",
            "action" => "tuitionFeeScheduleSlot.{$action}",
            "authAdmin" => $authAdmin,
            "data" => $feeSchedule,
            "message" => "Tuition Fee Schedule Slots {$label}",
        ]);

        StudentActionEvent::dispatch([
            'schoolBranch' => $currentSchool->id,
            'specialtyIds' => [$feeSchedule->schoolYear->specialty_id],
            'feature' => $studentFeature,
            'message' => "Tuition Fee Schedule {$label}",
            'data' => $feeSchedule,
        ]);
    }

    private function feeSchedule(object $currentSchool, string $feeScheduleId): FeeSchedule
    {
        $feeSchedule = FeeSchedule::where('school_branch_id', $currentSchool->id)
            ->with(['schoolYear.specialty.level', 'schoolYear.systemAcademicYear'])
            ->find($feeScheduleId);

        if (!$feeSchedule || !$feeSchedule->schoolYear?->specialty) {
            throw new AppException(
                "Fee schedule not found.",
                404,
                "Fee Schedule Not Found",
                "The fee schedule does not exist, or its academic year or specialty was removed.",
                null
            );
        }

        return $feeSchedule;
    }
}
