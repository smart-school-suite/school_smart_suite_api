<?php

namespace App\Models\SemesterTimetable;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SemesterTimetableVersion extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'version_number',
        'label',
        'scheduler_status',
        'parent_version_id',
        'draft_id',
        'school_branch_id',
    ];

    public $incrementing = false;
    public $table = 'timetable_versions';
    public $keyType = 'string';

    public function semesterActiveTimetable(): HasMany
    {
        return $this->hasMany(SemesterActiveTimetable::class);
    }
    public function draft()
    {
        return $this->belongsTo(SemesterTimetableDraft::class, 'draft_id');
    }

    public function parentVersion()
    {
        return $this->belongsTo(SemesterTimetableVersion::class, 'parent_version_id');
    }

    public function resultVersion()
    {
        return $this->hasMany(SemesterTimetableVersion::class, 'parent_version_id');
    }

    public function baseVersion()
    {
        return $this->hasMany(SemesterTimetablePrompt::class);
    }
}
