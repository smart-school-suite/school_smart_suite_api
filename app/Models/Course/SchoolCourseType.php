<?php

namespace App\Models\Course;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\Pivot;
class SchoolCourseType extends Pivot
{
    use GeneratesUuid;
    protected $fillable = [
       "school_branch_id",
       "course_type_id",
       "course_id"
    ];

    public $table = "school_course_types";
    public $incrementing = false;
    public $keyType =  'string';
}
