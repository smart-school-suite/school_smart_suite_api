<?php

namespace App\Models\Exam;

use App\Models\Courses;
use App\Models\GradeScale\SchoolGradeScale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamScore extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'school_branch_id',
        'candidate_id',
        'course_id',
        'exam_id',
        'grade_id',
        'score'
    ];

    public $incrementing = false;
    public $table = "exam_scores";
    public $keyType = 'string';

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Courses::class, 'course_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(ExamCandidate::class, 'candidate_id');
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(SchoolGradeScale::class, 'grade_id');
    }
}
