<?php

namespace App\Models\Promotion;

use App\Models\AcademicYear\SchoolAcademicYear;
use App\Models\Schoolbranches;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromotionRun extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'status',
        'current_stage',
        'total_students',
        'processed_students',
        'promoted_count',
        'repeated_count',
        'graduated_count',
        'review_count',
        'excluded_count',
        'failed_count',
        'initiated_by',
        'started_at',
        'completed_at',
        'metadata',
        'school_year_id',
        'school_branch_id'
    ];

    public $incrementing = false;
    public $keyType = 'string';
    public $table = "promotion_runs";

    public $casts = [
        'completed_at' => 'datetime',
        'started_at' => 'datetime',
        'promoted_count' => 'integer',
        'repeated_count' => 'integer',
        'graduated_count' => 'integer',
        'review_count' => 'integer',
        'excluded_count' => 'integer',
        'failed_count' => 'integer',
        'total_students' => 'integer',
        'processed_students' => 'integer',
    ];

    public function studentPromotionRecord(): HasMany
    {
        return $this->hasMany(StudentPromotionRecord::class,  'promotion_run_id');
    }
    public function schoolBranch(): BelongsTo
    {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolAcademicYear::class, 'school_year_id');
    }
}
