<?php

namespace App\Models\ExamTimetable;

use App\Models\Courses;
use App\Models\Exams;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamTimetableSlot extends Model
{
    use HasUuids;
    protected $fillable = [
        'date',
        'start_time',
        'end_time',
        'course_id',
        'version_id',
        'exam_id',
        'school_branch_id'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'date' => 'date'
    ];
    public $incrementing = false;
    public $table = "exam_timetable_slots";
    public $keyType = 'string';

    public function examSessionHall(): HasMany
    {
        return $this->hasMany(ExamSessionHall::class);
    }
    public function course(): BelongsTo
    {
        return $this->belongsTo(Courses::class, 'course_id');
    }
    public function version(): BelongsTo
    {
        return $this->belongsTo(ExamTimetableVersion::class, 'version_id');
    }
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exams::class, 'exam_id');
    }

    public function examSessionInvig(): HasMany
    {
        return $this->hasMany(ExamSessionInvigilator::class);
    }
}
