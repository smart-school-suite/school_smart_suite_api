<?php

namespace App\Models;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Studentresit extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'school_branch_id',
        'student_id',
        'course_id',
        'exam_id',
        'specialty_id',
        'level_id',
        'paid_status',
        'student_batch_id',
        'resit_fee',
        'attempt_number',
        'iscarry_over'
    ];

    public $keyType = 'string';
    public $table = 'student_resit';
    public $incrementing = 'false';

    public function studentResitTransactions(): HasMany
    {
        return $this->hasMany(ResitFeeTransactions::class, 'resitfee_id');
    }
    public function courses(): BelongsTo
    {
        return $this->belongsTo(Courses::class, 'course_id');
    }
    public function level(): BelongsTo
    {
        return $this->belongsTo(Educationlevels::class, 'level_id');
    }
    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exams::class, 'exam_id');
    }

}
