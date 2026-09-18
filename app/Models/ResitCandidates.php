<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResitCandidates extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'resit_exam_id',
        'student_id',
        'school_branch_id'
    ];

    public $incrementing = false;
    public $table = 'resit_candidates';
    public $keyType = 'string';
    public function resitScores(): HasMany
    {
        return $this->hasMany(ResitMarks::class, "candidate_id");
    }
    public function resitExam()
    {
        return $this->belongsTo(ResitExam::class, 'resit_exam_id');
    }
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
