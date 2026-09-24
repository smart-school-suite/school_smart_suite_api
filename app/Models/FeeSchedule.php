<?php

namespace App\Models;

use App\Models\AcademicYear\SchoolAcademicYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class FeeSchedule extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'school_year_id',
        'school_branch_id'
    ];

    public $table = 'fee_schedules';
    public $incrementing = 'false';
    public $keyType = 'string';

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolAcademicYear::class, 'school_year_id');
    }
    public function feeScheduleSlot(): HasMany
    {
        return $this->hasMany(FeeScheduleSlot::class);
    }
    public function studentFeeSchedule(): HasMany
    {
        return $this->hasMany(StudentFeeSchedule::class);
    }
    public function schoolSemester(): BelongsTo
    {
        return $this->belongsTo(SchoolSemester::class, 'school_semester_id');
    }
}
