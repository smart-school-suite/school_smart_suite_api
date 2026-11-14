<?php

namespace App\Models\ResitTimetable;

use App\Models\ExamTimetable\ExamTimetableVersion;
use App\Models\ResitExam;
use App\Models\Resitexamtimetable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A generated resit timetable. Statuses are the exam timetable's. */
class ResitTimetableVersion extends Model
{
    use HasUuids;

    public const STATUS_PENDING = ExamTimetableVersion::STATUS_PENDING;
    public const STATUS_GENERATING = ExamTimetableVersion::STATUS_GENERATING;
    public const STATUS_OPTIMAL = ExamTimetableVersion::STATUS_OPTIMAL;
    public const STATUS_PARTIAL = ExamTimetableVersion::STATUS_PARTIAL;
    public const STATUS_FAILED = ExamTimetableVersion::STATUS_FAILED;
    public const STATUS_ERROR = ExamTimetableVersion::STATUS_ERROR;

    protected $attributes = [
        'scheduler_status' => self::STATUS_PENDING,
    ];

    protected $fillable = [
        'version_number',
        'label',
        'scheduler_status',
        'resit_exam_id',
        'school_branch_id',
    ];

    protected $casts = [
        'version_number' => 'integer',
    ];

    public $incrementing = false;
    public $table = 'resit_timetable_versions';
    public $keyType = 'string';

    public static function createNext(string $schoolBranchId, string $resitExamId, string $status = self::STATUS_PENDING): self
    {
        $nextNumber = (self::where('school_branch_id', $schoolBranchId)
            ->where('resit_exam_id', $resitExamId)
            ->max('version_number') ?? 0) + 1;

        return self::create([
            'version_number' => $nextNumber,
            'label' => "Version {$nextNumber}",
            'scheduler_status' => $status,
            'school_branch_id' => $schoolBranchId,
            'resit_exam_id' => $resitExamId,
        ]);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(Resitexamtimetable::class, 'version_id');
    }

    public function resitExam(): BelongsTo
    {
        return $this->belongsTo(ResitExam::class, 'resit_exam_id');
    }

    /** Activation writes only the active version's sessions, so the version with slots is the active one. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereHas('slots');
    }
}
