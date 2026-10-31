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

    public const STATUS_PENDING = 'pending';
    public const STATUS_GENERATING = 'generating';
    public const STATUS_OPTIMAL = 'optimal';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_FAILED = 'failed';
    public const STATUS_ERROR = 'error';

    protected $attributes = [
        'scheduler_status' => self::STATUS_PENDING,
    ];

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

    public static function createNext(string $schoolBranchId, string $schoolSemesterId, string $status = self::STATUS_PENDING): self
    {
        $nextNumber = (self::where('school_branch_id', $schoolBranchId)
            ->where('school_semester_id', $schoolSemesterId)
            ->max('version_number') ?? 0) + 1;

        return self::create([
            'version_number' => $nextNumber,
            'label' => "Version {$nextNumber}",
            'scheduler_status' => $status,
            'school_branch_id' => $schoolBranchId,
            'school_semester_id' => $schoolSemesterId,
        ]);
    }

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
