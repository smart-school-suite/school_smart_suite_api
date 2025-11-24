<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentFeeSchedule extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'student_id',
        'expected_amount',
        'fee_schedule_slot_id',
        'fee_schedule_id',
        'gramification',
        'status',
        'tuition_fee_id',
        'amount_paid',
        'amount_left',
        'percentage_paid',
        'level_id',
        'specialty_id'
    ];

    protected $casts = [
        'expected_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'percentage_paid' => 'decimal:2'
    ];
    public $table = 'student_fee_schedules';
    public $incrementing = 'false';
    public $keyType = 'string';

    public function tuitionFee(): BelongsTo {
         return $this->belongsTo(TuitionFees::class, 'tuition_fee_id');
    }
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function feeScheduleSlot(): BelongsTo
    {
        return $this->belongsTo(FeeScheduleSlot::class, 'fee_schedule_slot_id');
    }

    public function feeSchedule(): BelongsTo
    {
        return $this->belongsTo(FeeSchedule::class, 'fee_schedule_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Educationlevels::class, 'level_id');
    }
    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }
}
