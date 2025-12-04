<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResitExam extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'start_date',
        'end_date',
        'weighted_mark',
        'timetable_published',
        'status',
        'grading_added',
        'expected_candidate_number',
        'evaluated_candidate_number',
        'school_branch_id',
        'school_year',
        'grades_category_id'
    ];

    protected $cast = [
        'start_date' => 'date',
        'end_date' => 'date',
        'weighted_mark' => 'decimal:2',
    ];
    public $incrementing = 'false';
    public $table = 'resit_exams';
    public $keyType = 'string';

    public function resitExamRef(): HasMany
    {
        return $this->hasMany(ResitExamRef::class);
    }
    public function resitMarks(): HasMany
    {
        return $this->hasMany(ResitMarks::class, 'resit_exam_id');
    }
    public function resitExamTimetable(): BelongsTo
    {
        return $this->belongsTo(Resitexamtimetable::class, 'resit_exam_id');
    }
    public function schoolBranch(): BelongsTo
    {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }
    public function resitResults(): HasMany
    {
        return $this->hasMany(ResitResults::class, 'resit_exam_id');
    }
    public function resitCandidates(): HasMany
    {
        return $this->hasMany(ResitCandidates::class, 'resit_exam_id');
    }
    public function gradesCategory(): BelongsTo
    {
        return $this->belongsTo(GradesCategory::class, 'grades_category_id');
    }
}
