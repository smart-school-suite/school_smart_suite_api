<?php

namespace App\Models;

use App\Models\GradeScale\SchoolGradeScale;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResitMarks extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'school_branch_id',
        'candidate_id',
        'resit_id',
        'resit_exam_id',
        'grade_id',
        'score'
    ];

    protected $cast = [
        'score' => 'decimal:2',
    ];
    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'resit_marks';

    public function resitExam(): BelongsTo
    {
        return $this->belongsTo(ResitExam::class, 'resit_exam_id');
    }
    public function resit(): BelongsTo
    {
        return $this->belongsTo(Studentresit::class, "resit_id");
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(ResitCandidates::class,  'candidate_id');
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(SchoolGradeScale::class, 'grade_id');
    }
}
