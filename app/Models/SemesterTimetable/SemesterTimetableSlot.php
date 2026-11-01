<?php

namespace App\Models\SemesterTimetable;

use App\Models\Courses;
use App\Models\Hall;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SemesterTimetableSlot extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'school_branch_id',
        'course_id',
        'teacher_id',
        'day',
        'start_time',
        'end_time',
        'break',
        'hall_id',
        'version_id',
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
    public function course(): BelongsTo
    {
        return $this->belongsTo(Courses::class, 'course_id');
    }
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
    public function semesterTimetableVersion(): BelongsTo
    {
        return $this->belongsTo(SemesterTimetableVersion::class, 'version_id');
    }
}
