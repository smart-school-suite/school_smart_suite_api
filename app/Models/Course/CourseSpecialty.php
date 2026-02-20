<?php

namespace App\Models\Course;

use Illuminate\Database\Eloquent\Model;
use App\Models\Courses;
use App\Models\Specialty;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseSpecialty extends Model
{
    use GeneratesUuid;

    protected $fillable = [
        'course_id',
        'specialty_id',
        'school_branch_id'
    ];

    public $keyType = 'string';
    public $incrementing = false;
    public $table = "course_specialties";

    public function course(): BelongsTo
    {
        return $this->belongsTo(Courses::class, 'course_id');
    }

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }
}
