<?php

namespace App\Models\SemesterTimetable;

use App\Models\SchoolSemester;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class SemesterTimetableDraft extends Model
{
    use GeneratesUuid, HasFactory;
    protected $fillable = [
        'name',
        'draft_count',
        'school_semester_id',
        'school_branch_id',
    ];

    public $incrementing = false;
    protected $table = 'timetable_drafts';
    protected $keyType = 'string';

    protected $casts = [
        'draft_count'
    ];
    public function schoolSemester()
    {
        return $this->belongsTo(SchoolSemester::class, 'school_semester_id');
    }

    public function semesterTimetableVersions()
    {
        return $this->hasMany(SemesterTimetableVersion::class, 'draft_id');
    }

    public function semesterTimetablePrompt(): HasMany
    {
        return $this->hasMany(SemesterTimetablePrompt::class);
    }
}
