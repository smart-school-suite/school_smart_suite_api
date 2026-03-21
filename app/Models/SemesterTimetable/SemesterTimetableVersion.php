<?php

namespace App\Models\SemesterTimetable;

use App\Models\SchoolSemester;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MongoDB\Laravel\Relations\BelongsTo;

class SemesterTimetableVersion extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'version_number',
        'label',
        'scheduler_status',
        'school_branch_id',
        'scheduler_input',
        'scheduler_output',
        'school_semester_id',
    ];

    public $incrementing = false;
    public $table = 'timetable_versions';
    public $keyType = 'string';
    protected $casts = [
        'scheduler_input' => 'json',
        'scheduler_output' => 'json',
    ];

    public function semesterActiveTimetable(): HasMany
    {
        return $this->hasMany(SemesterActiveTimetable::class);
    }

    public function parentVersion()
    {
        return $this->belongsTo(SemesterTimetableVersion::class, 'parent_version_id');
    }

    public function resultVersion()
    {
        return $this->hasMany(SemesterTimetableVersion::class, 'parent_version_id');
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
