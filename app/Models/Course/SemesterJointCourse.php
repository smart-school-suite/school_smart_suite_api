<?php

namespace App\Models\Course;

use App\Models\AcademicYear\SchoolAcademicYear;
use App\Models\Courses;
use App\Models\Semester;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Relations\HasMany;

class SemesterJointCourse extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'school_branch_id',
        'semester_id',
        'course_id',
        'school_year_id'
    ];

    public function jointCourseSlots(): HasMany
    {
        return $this->hasMany(JointCourseSlot::class, 'semester_joint_course_id');
    }

    public function semesterJointCourseRef(): HasMany
    {
        return $this->hasMany(SemesterJoinCourseReference::class);
    }
    public function course(): BelongsTo
    {
        return $this->belongsTo(Courses::class, 'course_id');
    }
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }
    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolAcademicYear::class, 'school_year_id');
    }
}
