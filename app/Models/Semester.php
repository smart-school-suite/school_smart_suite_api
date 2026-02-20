<?php

namespace App\Models;

use App\Models\Course\JointCourseSlot;
use App\Models\Course\SemesterJointCourse;
use App\Models\SemesterTimetable\SemesterTimetableSlot;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Semester extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'name',
        'count',
        'program_name'
    ];

    protected $cast = [
        'count' => 'integer'
    ];
    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'semesters';

    public function semesterJointCourse(): HasMany
    {
        return $this->hasMany(SemesterJointCourse::class, 'semester_id');
    }
    public function resitExamRef(): HasMany
    {
        return $this->hasMany(ResitExamRef::class);
    }
    public function studentResit(): HasMany
    {
        return $this->hasMany(StudentResit::class);
    }
    public function exams(): HasMany
    {
        return $this->hasMany(Exams::class);
    }
    public function examtype(): HasMany
    {
        return $this->hasMany(Examtype::class);
    }
    public function semesterTimetableSlot(): HasMany
    {
        return $this->hasMany(SemesterTimetableSlot::class);
    }
    public function courses(): HasMany
    {
        return $this->hasMany(Courses::class);
    }
}
