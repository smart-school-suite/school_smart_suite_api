<?php

namespace App\Models\Course;

use App\Models\SchoolSemester;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SemesterJoinCourseReference extends Model
{
    use GeneratesUuid;

    protected $fillable = [
        'semester_joint_course_id',
        'school_semester_id',
        'school_branch_id'
    ];

    public $incrementing = false;
    public $table = "semester_joint_course_refs";
    public $keyType = 'string';

    public function schoolSemester(): BelongsTo
    {
        return $this->belongsTo(SchoolSemester::class, 'school_semester_id');
    }

    public function semesterJointCourse(): BelongsTo
    {
        return $this->belongsTo(SemesterJointCourse::class, 'semester_joint_course_id');
    }
}
