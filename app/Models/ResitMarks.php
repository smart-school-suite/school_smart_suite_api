<?php

namespace App\Models;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResitMarks extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'student_id',
        'courses_id',
        'student_batch_id',
        'resit_exam_id',
        'level_id',
        'score',
        'specialty_id',
        'school_branch_id',
        'grade',
        'grade_status',
        'grade_points',
        'gratification',
    ];

    protected $cast = [
        'score' => 'decimal:2',
    ];
    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'resit_marks';

    public function resitExam()
    {
        return $this->belongsTo(ResitExam::class, 'resit_exam_id');
    }
    public function course(): BelongsTo {
        return $this->belongsTo(Courses::class, 'courses_id');
    }

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class, 'student_id');
    }


    public function level(): BelongsTo {
        return $this->belongsTo(Educationlevels::class, 'level_id');
    }

    public function specialty(): BelongsTo {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }
}
