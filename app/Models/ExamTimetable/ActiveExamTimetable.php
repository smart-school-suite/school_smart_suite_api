<?php

namespace App\Models\ExamTimetable;

use App\Models\Exams;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActiveExamTimetable extends Model
{
    use HasUuids;
    protected $fillable = [
        'version_id',
        'exam_id',
        'school_branch_id'
    ];

    public $incrementing = false;
    public $table = "active_exam_timetable";
    public $keyType = 'string';

    public function version(): BelongsTo
    {
        return $this->belongsTo(ExamTimetableVersion::class, 'version_id');
    }
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exams::class, 'exam_id');
    }

}
