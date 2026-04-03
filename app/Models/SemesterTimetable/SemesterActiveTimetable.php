<?php

namespace App\Models\SemesterTimetable;

use App\Models\SchoolSemester;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class SemesterActiveTimetable extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'school_branch_id',
        'timetable_version_id',
        'school_semester_id',
    ];

    public $incrementing = false;
    protected $table = 'active_timetables';
    protected $keyType = 'string';

    public function timetableVersion(): BelongsTo
    {
        return $this->belongsTo(SemesterTimetableVersion::class, 'timetable_version_id');
    }

    public function schoolSemester(): BelongsTo
    {
        return $this->belongsTo(SchoolSemester::class, 'school_semester_id');
    }
}
