<?php

namespace App\Models;

use App\Models\AcademicYear\SchoolAcademicYear;
use App\Models\Course\SemesterJoinCourseReference;
use App\Models\SemesterTimetable\SemesterTimetableVersion;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolSemester extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'start_date',
        'end_date',
        'school_year_id',
        'semester_id',
        'timetable_published',
        'school_branch_id',
    ];

    protected $cast = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    public $incrementing = false;
    public $table = 'school_semesters';
    public $keyType = 'string';

    public function semesterJointCourseReference(): HasMany
    {
        return $this->hasMany(SemesterJoinCourseReference::class, 'school_semester_id');
    }
    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolAcademicYear::class, 'school_year_id');
    }
    public function scopeStillRunning(Builder $query): Builder
    {
        return $query->whereDate('end_date', '>=', now()->toDateString())
            ->where(fn(Builder $status) => $status->whereNull('status')->orWhere('status', '!=', 'expired'));
    }

    public function isStillRunning(): bool
    {
        return $this->status !== 'expired' && Carbon::parse($this->end_date)->endOfDay()->isFuture();
    }
    public function instructorAvailability(): HasMany
    {
        return $this->hasMany(InstructorAvailability::class, 'school_semester_id');
    }
    public function feeSchedule(): HasMany
    {
        return $this->hasMany(FeeSchedule::class);
    }
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }
    public function schoolBranch(): BelongsTo
    {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }
    public function semesterTimetableVersion(): HasMany
    {
        return $this->hasMany(SemesterTimetableVersion::class, 'school_semester_id');
    }
}
