<?php

namespace App\Models\SemesterTimetable;

use App\Models\SchoolSemester;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;

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

    public function timetableVersion()
    {
        return $this->belongsTo(SemesterTimetableVersion::class, 'timetable_version_id');
    }

    public function schoolSemester()
    {
        return $this->belongsTo(SchoolSemester::class, 'school_semester_id');
    }
}
