<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeScheduleSlot extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'due_date',
        'fee_percentage',
        'amount',
        'installment_id',
        'fee_schedule_id'
    ];

    protected $cast = [
         'due_date' => 'date',
         'amount' => 'decimal:2',
         'fee_percentage' => 'decimal:2'
    ];
    public $incrementing = 'false';
    public $keyType = 'string';
    public $table = 'fee_schedule_slots';

    public function studentFeeSchedule(): HasMany {
        return $this->hasMany(StudentFeeSchedule::class);
    }

    public function feeSchedule(): BelongsTo {
        return $this->belongsTo(FeeSchedule::class, 'fee_schedule_id');
    }

    public function installment(): BelongsTo {
        return $this->belongsTo(Installment::class, 'installment_id');
    }
}
