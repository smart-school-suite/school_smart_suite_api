<?php

namespace App\Models;

use App\Models\Course\CourseSpecialty;
use App\Models\Course\CourseType;
use App\Models\Course\JointCourseSlot;
use App\Models\Course\SchoolCourseType;
use App\Models\Course\SemesterJointCourse;
use App\Models\SemesterTimetable\SemesterTimetableSlot;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Courses extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'course_code',
        'course_title',
        'school_branch_id',
        'credit',
        'status',
        'description',
        'semester_id'
    ];

    protected $casts = [
        'credit' => 'integer',
    ];
    public $keyType = 'string';
    public $table = 'courses';
    public $incrementing = false;

    public function semesterJointCourse(): HasMany
    {
        return $this->hasMany(SemesterJointCourse::class);
    }
    public function jointCourseSlot(): HasMany
    {
        return $this->hasMany(JointCourseSlot::class);
    }
    public function courseSpecialty(): HasMany
    {
        return $this->hasMany(CourseSpecialty::class, 'course_id');
    }
    public function teacherCoursePreference(): HasMany
    {
        return $this->hasMany(TeacherCoursePreference::class, 'course_id');
    }

    public function resitmarks(): HasMany
    {
        return $this->hasMany(ResitMarks::class, 'course_id');
    }
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exams::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function marks(): HasMany
    {
        return $this->hasMany(Marks::class, 'courses_id');
    }

    public function types()
    {
        return $this->belongsToMany(
            CourseType::class,
            'school_course_types',
            'course_id',
            'course_type_id'
        )
            ->using(SchoolCourseType::class)
            ->withPivot(['id', 'school_branch_id'])
            ->withTimestamps();
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function schoolbranches(): BelongsTo
    {
        return $this->belongsTo(Schoolbranches::class);
    }

    public function examtimetable(): HasMany
    {
        return $this->hasMany(Examtimetable::class);
    }
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }
    public function studentresit(): HasMany
    {
        return $this->hasMany(Studentresit::class);
    }

    public function semesterTimetableSlot(): HasMany
    {
        return $this->hasMany(SemesterTimetableSlot::class);
    }
}
