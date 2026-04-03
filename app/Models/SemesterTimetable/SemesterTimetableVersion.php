<?php

namespace App\Models\SemesterTimetable;

use App\Models\SchoolSemester;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SemesterTimetableVersion extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'version_number',
        'label',
        'scheduler_status',
        'school_branch_id',
        'school_semester_id',
    ];

    public $incrementing = false;
    public $table = 'timetable_versions';
    public $keyType = 'string';

    public function semesterActiveTimetable(): HasMany
    {
        return $this->hasMany(SemesterActiveTimetable::class, 'timetable_version_id');
    }

    public function timeTableSlot(): HasMany
    {

        return $this->hasMany(SemesterTimetableSlot::class, 'version_id');
    }

    public function schoolSemester(): BelongsTo
    {
        return $this->belongsTo(SchoolSemester::class, 'school_semester_id');
    }
}
