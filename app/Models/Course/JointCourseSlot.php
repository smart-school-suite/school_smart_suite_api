<?php

namespace App\Models\Course;

use App\Models\AcademicYear\SchoolAcademicYear;
use App\Models\Courses;
use App\Models\Hall;
use App\Models\Teacher;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JointCourseSlot extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'start_time',
        'end_time',
        'day',
        'school_branch_id',
        'course_id',
        'hall_id',
        'teacher_id',
        'semester_joint_course_id'
    ];

    public $incrementing = false;
    public $table = 'joint_course_slots';
    public $keyType = 'string';

    public function course(): BelongsTo
    {
        return $this->belongsTo(Courses::class, 'course_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class, 'hall_id');
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolAcademicYear::class, 'school_year_id');
    }

    public function semesterJointCourse(): BelongsTo
    {
        return $this->belongsTo(SemesterJointCourse::class, 'semester_joint_course_id');
    }
}
