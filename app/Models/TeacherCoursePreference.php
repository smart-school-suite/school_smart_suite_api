<?php

namespace App\Models;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherCoursePreference extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'teacher_id',
        'course_id',
        'school_branch_id'
    ];

    public $keyType = 'string';
    public $table = 'teacher_course_preferences';
    public $incrementing = 'false';

    public function teacher():BelongsTo {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function course(): BelongsTo {
        return $this->belongsTo(Courses::class, 'course_id');
    }
}
