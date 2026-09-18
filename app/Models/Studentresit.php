<?php

namespace App\Models;

use App\Models\Exam\Exam;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
class Studentresit extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'school_branch_id',
        'student_id',
        'course_id',
        'exam_id',
        'payment_status',
        'fee',
        'attempts',
        'iscarry_over'
    ];

    public $keyType = 'string';
    public $table = 'student_resits';
    public $incrementing = 'false';

    public function studentResitTransactions(): HasMany
    {
        return $this->hasMany(ResitFeeTransactions::class, 'resitfee_id');
    }
    public function courses(): BelongsTo
    {
        return $this->belongsTo(Courses::class, 'course_id');
    }
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

}
