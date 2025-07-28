<?php

namespace App\Models;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class ResitCandidates extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'id',
        'resit_exam_id',
        'student_id',
        'school_branch_id',
        'grades_submitted',
        'student_accessed',
    ];

    public $incrementing = false;
    public $table = 'resit_candidates';
    public $keyType = 'string';
    protected $casts = [
        'grades_submitted' => 'boolean',
        'student_accessed' => 'boolean',
    ];
    public function resitExam()
    {
        return $this->belongsTo(ResitExam::class, 'resit_exam_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

}
