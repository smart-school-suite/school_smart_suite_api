<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResitExamRef extends Model
{
    use HasUuids;
    protected $fillable = [
        'school_branch_id',
        'exam_id',
        'resit_exam_id'
    ];

    public $incrementing = 'false';
    public $keyType = 'string';
    public $table = 'resit_exam_references';

    public function examType(): BelongsTo
    {
        return $this->belongsTo(Examtype::class, 'exam_type_id');
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exams::class, 'exam_id');
    }
    public function resitExam(): BelongsTo {
         return $this->belongsTo(ResitExam::class, 'resit_exam_id');
    }
}
