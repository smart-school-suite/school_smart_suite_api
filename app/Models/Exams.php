<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Exams extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'school_branch_id',
        'exam_type_id',
        'start_date',
        'end_date',
        'level_id',
        'department_id',
        'weighted_mark',
        'semester_id',
        'school_year',
        'status',
        'timetable_published',
        'specialty_id',
        'expected_candidate_number',
        'evaluated_candidate_number',
        'student_batch_id',
        'grades_category_id'
    ];

    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'exams';

    public function resitmarks(): HasMany
    {
        return $this->hasMany(ResitMarks::class, 'exam_id');
    }

    public function examResit(): HasMany {
        return $this->hasMany(ResitExam::class);
    }
    public function courses(): BelongsTo
    {
        return $this->belongsTo(Exams::class);
    }
    public function resitResults(): HasMany
    {
        return $this->hasMany(ResitResults::class);
    }
    public function studentResults(): HasMany
    {
        return $this->hasMany(StudentResults::class);
    }
    public function studentBatch(): BelongsTo
    {
        return $this->belongsTo(Studentbatch::class, 'student_batch_id');
    }
    public function accessedStudent(): HasMany
    {
        return $this->hasMany(AccessedStudent::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function marks(): HasMany
    {
        return $this->hasMany(Marks::class, 'exam_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function schoolbranches(): BelongsTo
    {
        return $this->belongsTo(Schoolbranches::class);
    }

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function grade(): HasMany
    {
        return $this->hasMany(Grades::class);
    }

    public function examtype(): BelongsTo
    {
        return $this->belongsTo(Examtype::class, 'exam_type_id');
    }

    public function examtimetable(): HasMany
    {
        return $this->hasMany(Examtimetable::class, 'exam_id');
    }

    public function studentresit(): HasMany
    {
        return $this->hasMany(Studentresit::class);
    }
    public function level(): BelongsTo
    {
        return $this->belongsTo(Educationlevels::class);
    }
}
