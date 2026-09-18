<?php

namespace App\Models;

use App\Models\AcademicYear\SchoolAcademicYear;
use App\Models\GradeScale\SchoolGradeScaleCategory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResitExam extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = [
        'start_date',
        'end_date',
        'max_score',
        'school_year_id',
        'grades_category_id',
        'school_branch_id',
        'exam_type_id'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'max_score' => 'float',
    ];
    public $incrementing = 'false';
    public $table = 'resit_exams';
    public $keyType = 'string';

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolAcademicYear::class, "school_year_id");
    }
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
     public function examGradeScale(): BelongsTo
    {
        return $this->belongsTo(SchoolGradeScaleCategory::class, 'grades_category_id');
    }

    public function examType(): BelongsTo
    {
        return $this->belongsTo(Examtype::class, "exam_type_id");
    }
}
