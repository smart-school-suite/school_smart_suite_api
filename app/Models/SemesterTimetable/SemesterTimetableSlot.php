<?php

namespace App\Models\SemesterTimetable;

use App\Models\Courses;
use App\Models\Educationlevels;
use App\Models\Hall;
use App\Models\SchoolSemester;
use App\Models\Semester;
use App\Models\Specialty;
use App\Models\Studentbatch;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SemesterTimetableSlot extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'school_branch_id',
        'specialty_id',
        'level_id',
        'course_id',
        'teacher_id',
        'day',
        'start_time',
        'end_time',
        'school_semester_id',
        'break',
        'hall_id',
        'student_batch_id',
        'timetable_version_id',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'break' => 'boolean'
    ];

    public $keyType = 'string';
    public $incrementing = false;
    public $table = 'timetable_slots';

    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class, 'hall_id');
    }
    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Courses::class, 'course_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Educationlevels::class, 'level_id');
    }
    public function schoolSemester(): BelongsTo
    {
        return $this->belongsTo(SchoolSemester::class, 'school_semester_id');
    }
    public function studentBatch(): BelongsTo
    {
        return $this->belongsTo(Studentbatch::class, 'student_batch_id');
    }

    public function semesterTimetableVersion(): BelongsTo
    {
        return $this->belongsTo(SemesterTimetableVersion::class, 'timetable_version_id');
    }
}
