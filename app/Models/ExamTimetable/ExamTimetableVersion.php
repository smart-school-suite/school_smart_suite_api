<?php

namespace App\Models\ExamTimetable;

use App\Models\Exams;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamTimetableVersion extends Model
{
    //
    use HasUuids;
    protected $fillable = [
        'version_number',
        'number',
        'exam_id',
        'school_branch_id'
    ];

    protected $casts = [
        'number' => "integer"
    ];
    public $incrementing = false;
    public $table = "exam_timetable_versions";
    public $keyType = 'string';

    public function examTimetableSlot(): HasMany
    {
        return $this->hasMany(ExamTimetableSlot::class);
    }
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exams::class, 'exam_id');
    }

    public function activeTimetable(): HasMany
    {
        return $this->hasMany(ActiveExamTimetable::class);
    }
}
