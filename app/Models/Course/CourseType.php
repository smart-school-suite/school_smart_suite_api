<?php

namespace App\Models\Course;

use App\Models\Courses;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;

class CourseType extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'name',
        'key',
        'description',
        'color_text',
        'background_color'
    ];

    public $table = 'course_types';
    public $incrementing = false;
    public $keyType = 'string';

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
}
