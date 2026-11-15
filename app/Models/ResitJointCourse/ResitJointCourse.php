<?php

namespace App\Models\ResitJointCourse;

use App\Models\AcademicYear\SystemAcademicYear;
use App\Models\Courses;
use App\Models\Examtype;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A course several specialties resit together, in one academic year and resit exam type. */
class ResitJointCourse extends Model
{
    use HasUuids;

    protected $fillable = ['school_branch_id', 'course_id', 'school_year_id', 'exam_type_id'];

    public $incrementing = false;
    public $table = 'resit_jcs';
    public $keyType = 'string';

    public function refs(): HasMany
    {
        return $this->hasMany(ResitJointCourseRef::class, 'resit_jc_id');
    }

    public function slots(): HasMany
    {
        return $this->hasMany(ResitJointCourseSlot::class, 'resit_jc_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Courses::class, 'course_id');
    }

    public function examType(): BelongsTo
    {
        return $this->belongsTo(Examtype::class, 'exam_type_id');
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SystemAcademicYear::class, 'school_year_id');
    }
}
