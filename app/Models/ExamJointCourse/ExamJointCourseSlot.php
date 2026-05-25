<?php

namespace App\Models\ExamJointCourse;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamJointCourseSlot extends Model
{
    use HasUuids;

    protected $fillable = [
        'school_branch_id',
        'exam_jc_id',
        'date',
        'start_time',
        'end_time'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'date' => 'date'
    ];

    public $incrementing = false;
    public $table = 'exam_jc_slots';
    public $keyType = 'string';

    public function examJointCourse(): BelongsTo
    {
        return $this->belongsTo(ExamJointCourse::class, 'exam_jc_id');
    }

    public function examJcSessionHall(): HasMany
    {
        return $this->hasMany(ExamJCSessionHall::class);
    }
}
