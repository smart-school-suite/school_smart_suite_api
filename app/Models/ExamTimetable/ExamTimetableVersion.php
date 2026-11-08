<?php

namespace App\Models\ExamTimetable;

use App\Models\Exams;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamTimetableVersion extends Model
{
    use HasUuids;

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
        'exam_id',
        'school_branch_id'
    ];

    protected $casts = [
        'version_number' => 'integer'
    ];
    public $incrementing = false;
    public $table = "exam_timetable_versions";
    public $keyType = 'string';

    public static function createNext(string $schoolBranchId, string $examId, string $status = self::STATUS_PENDING): self
    {
        $nextNumber = (self::where('school_branch_id', $schoolBranchId)
            ->where('exam_id', $examId)
            ->max('version_number') ?? 0) + 1;

        return self::create([
            'version_number' => $nextNumber,
            'label' => "Version {$nextNumber}",
            'scheduler_status' => $status,
            'school_branch_id' => $schoolBranchId,
            'exam_id' => $examId,
        ]);
    }

    public function examTimetableSlot(): HasMany
    {
        return $this->hasMany(ExamTimetableSlot::class, 'version_id');
    }
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exams::class, 'exam_id');
    }

    public function activeTimetable(): HasMany
    {
        return $this->hasMany(ActiveExamTimetable::class, 'version_id');
    }
}
