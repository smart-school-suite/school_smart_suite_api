<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'specialty_id',
        'level_id',
        'school_semester_id',
        'config_status',
        'status'
    ];

    public $table = 'fee_schedules';
    public $incrementing = 'false';
    public $keyType = 'string';

    public function feeScheduleSlot(): HasMany {
        return $this->hasMany(FeeScheduleSlot::class);
    }
    public function studentFeeSchedule(): HasMany {
        return $this->hasMany(StudentFeeSchedule::class);
    }

    public function specialty(): BelongsTo {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }

    public function level(): BelongsTo {
        return $this->belongsTo(Educationlevels::class, 'level_id');
    }

    public function schoolSemester(): BelongsTo {
        return $this->belongsTo(SchoolSemester::class, 'school_semester_id');
    }
}
