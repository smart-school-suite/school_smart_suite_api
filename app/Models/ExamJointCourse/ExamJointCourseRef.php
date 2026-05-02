<?php

namespace App\Models\ExamJointCourse;

use App\Models\Exams;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamJointCourseRef extends Model
{
    use HasUuids;
    protected $fillable = [
        'school_branch_id',
        'exam_id',
        'exam_js_id'
    ];

    public $incrementing = false;
    public $table = 'exam_jc_refs';
    public $keyType = 'string';

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exams::class, 'exam_id');
    }
    public function examJointCourse(): BelongsTo
    {
        return $this->belongsTo(ExamJointCourse::class, 'exam_js_id');
    }
}
