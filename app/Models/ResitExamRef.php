<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResitExamRef extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'school_branch_id',
        'exam_type_id',
        'level_id',
        'exam_id',
        'semester_id',
        'specialty_id',
        'student_batch_id',
        'resit_exam_id'
    ];

    public $incrementing = 'false';
    public $keyType = 'string';
    public $table = 'resit_exam_references';

    public function examType(): BelongsTo
    {
        return $this->belongsTo(Examtype::class, 'exam_type_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Educationlevels::class, 'level_id');
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exams::class, 'exam_id');
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }

    public function studentBatch(): BelongsTo
    {
        return $this->belongsTo(Studentbatch::class, 'student_batch_id');
    }

    public function resitExam(): BelongsTo {
         return $this->belongsTo(ResitExam::class, 'resit_exam_id');
    }
}
