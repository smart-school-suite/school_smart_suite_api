<?php

namespace App\Models\ExamJointCourse;

use App\Models\AcademicYear\SystemAcademicYear;
use App\Models\Courses;
use App\Models\Semester;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamJointCourse extends Model
{
    use HasUuids;
    protected $fillable = [
        'school_branch_id',
        'course_id',
        'school_year_id',
        'semester_id'
    ];
    public $incrementing = false;
    public $table = 'exam_jcs';
    public $keyType = 'string';
    public function examJcRef(): HasMany
    {
        return $this->hasMany(ExamJointCourseRef::class);
    }
    public function examJointCourseSlot(): HasMany
    {
        return $this->hasMany(ExamJointCourseSlot::class);
    }
    public function course(): BelongsTo
    {
        return $this->belongsTo(Courses::class, 'course_id');
    }
    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SystemAcademicYear::class, 'school_year_id');
    }
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }
}
