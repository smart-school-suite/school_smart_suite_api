<?php

namespace App\Services\TuitionFee;

use App\Models\FeeSchedule;
use App\Models\FeeScheduleSlot;
use App\Models\TuitionFees;
use App\Models\TuitionFeeTransactions;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Derives a student's tuition fee progress, overall and per fee schedule
 * installment, from what is stored: the tuition fee record (the total owed
 * for an academic year), its transactions (what was paid and when) and the
 * academic year's fee schedule installments (the share due by each date).
 * Nothing is stored, so progress is always in step with payments and with
 * changes to the fee schedule.
 *
 * Payments are applied to installments in due date order: an installment is
 * paid once everything due before it is paid. An installment's gramification
 * is "paypunctual" when the payment completing it was made by its due date,
 * "late" when made after, and "pending" until it is completed.
 */
class TuitionFeeProgressService
{
    private const EPSILON = 0.001;

    /**
     * Progress of each of the student's tuition fee records, keyed by
     * tuition fee id. Loads every schedule and transaction in two queries.
     *
     * @param Collection<int, TuitionFees> $tuitionFees
     * @return Collection<string, array>
     */
    public function forTuitionFees(Collection $tuitionFees): Collection
    {
        if ($tuitionFees->isEmpty()) {
            return collect();
        }

        $transactions = TuitionFeeTransactions::whereIn('tuition_id', $tuitionFees->pluck('id'))
            ->orderBy('created_at')
            ->get()
            ->groupBy('tuition_id');

        $slots = $this->slotsByAcademicYear($tuitionFees->pluck('school_academic_year_id')->filter()->unique());

        return $tuitionFees->mapWithKeys(fn(TuitionFees $fee) => [
            $fee->id => $this->progress(
                $fee,
                $slots->get($fee->school_academic_year_id, collect()),
                $transactions->get($fee->id, collect()),
            ),
        ]);
    }

    public function forTuitionFee(TuitionFees $tuitionFee): array
    {
        return $this->forTuitionFees(collect([$tuitionFee]))->first();
    }

    /**
     * @param Collection<int, FeeScheduleSlot> $slots
     * @param Collection<int, TuitionFeeTransactions> $transactions oldest first
     */
    public function progress(TuitionFees $tuitionFee, Collection $slots, Collection $transactions): array
    {
        $total = (float) $tuitionFee->tution_fee_total;
        $paid = round((float) $transactions->sum('amount'), 2);
        $today = now()->startOfDay();

        $dueBefore = 0.0;
        $installments = [];

        foreach ($slots as $slot) {
            $expected = round($total * ((float) $slot->percentage / 100), 2);
            $paidTowards = round(min($expected, max(0, $paid - $dueBefore)), 2);
            $left = round($expected - $paidTowards, 2);
            $completed = $left <= self::EPSILON;
            $dueDate = $slot->due_date ? Carbon::parse($slot->due_date)->endOfDay() : null;
            $completedAt = $completed ? $this->paidUpToAt($transactions, $dueBefore + $expected) : null;

            $installments[] = [
                'slot_id' => $slot->id,
                'installment' => $slot->installment?->name,
                'installment_id' => $slot->installment_id,
                'installment_count' => $slot->installment?->count,
                'due_date' => $slot->due_date?->toDateString(),
                'fee_percentage' => (float) $slot->percentage,
                'expected_amount' => $expected,
                'amount_paid' => $paidTowards,
                'amount_left' => max(0, $left),
                'percentage_paid' => $expected > 0 ? round($paidTowards / $expected * 100, 2) : 100.0,
                'status' => $completed ? 'completed' : ($paidTowards > 0 ? 'inprogress' : 'unpaid'),
                'gramification' => match (true) {
                    !$completed => 'pending',
                    $dueDate === null, $completedAt === null || $completedAt->lessThanOrEqualTo($dueDate) => 'paypunctual',
                    default => 'late',
                },
                'is_overdue' => !$completed && $dueDate !== null && $dueDate->lt($today),
            ];

            $dueBefore += $expected;
        }

        return [
            'tuition_fee_id' => $tuitionFee->id,
            'school_academic_year_id' => $tuitionFee->school_academic_year_id,
            'specialty_id' => $tuitionFee->specialty_id,
            'tuition_fee_total' => $total,
            'amount_paid' => $paid,
            'amount_left' => round(max(0, $total - $paid), 2),
            'percentage_paid' => $total > 0 ? round(min(100, $paid / $total * 100), 2) : 100.0,
            'installments' => $installments,
        ];
    }

    /** The installments of each academic year's fee schedule, in due date order. */
    private function slotsByAcademicYear(Collection $academicYearIds): Collection
    {
        if ($academicYearIds->isEmpty()) {
            return collect();
        }

        $schedules = FeeSchedule::whereIn('school_year_id', $academicYearIds)->pluck('school_year_id', 'id');

        return FeeScheduleSlot::whereIn('fee_schedule_id', $schedules->keys())
            ->with('installment')
            ->get()
            ->sortBy(fn(FeeScheduleSlot $slot) => [$slot->due_date?->toDateString() ?? '9999-12-31', $slot->installment?->count ?? PHP_INT_MAX])
            ->groupBy(fn(FeeScheduleSlot $slot) => $schedules[$slot->fee_schedule_id])
            ->map->values();
    }

    /** When the payments first added up to the amount, if they ever did. */
    private function paidUpToAt(Collection $transactions, float $amount): ?Carbon
    {
        $running = 0.0;

        foreach ($transactions as $transaction) {
            $running += (float) $transaction->amount;
            if ($running + self::EPSILON >= $amount) {
                return Carbon::parse($transaction->created_at);
            }
        }

        return null;
    }
}
