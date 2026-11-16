<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One installment of an academic year's fee schedule: the share of the
 * tuition (percentage) due by due_date. Amounts are not stored: a slot's
 * amount is that percentage of each student's own tuition fee total, and
 * what a student has paid towards it is derived from their tuition fee
 * transactions (see TuitionFeeProgressService).
 */
class FeeScheduleSlot extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'due_date',
        'percentage',
        'installment_id',
        'fee_schedule_id',
        'school_branch_id',
    ];

    protected $casts = [
        'due_date' => 'date',
        'percentage' => 'float',
    ];

    public $incrementing = false;
    public $keyType = 'string';
    public $table = 'fee_sched_installments';

    public function feeSchedule(): BelongsTo
    {
        return $this->belongsTo(FeeSchedule::class, 'fee_schedule_id');
    }

    public function installment(): BelongsTo
    {
        return $this->belongsTo(Installment::class, 'installment_id');
    }
}
