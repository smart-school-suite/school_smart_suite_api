<?php

namespace App\Models\Election;

use App\Models\AcademicYear\SchoolAcademicYear;
use App\Models\Schoolbranches;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * An actual occupant of a position. Doubles as the election winners history.
 */
class ElectionOfficeHolder extends Model
{
    use HasFactory, HasUuids;

    public const APPOINTMENT_ELECTED = 'elected';
    public const APPOINTMENT_APPOINTED = 'appointed';
    public const APPOINTMENT_REPLACEMENT = 'replacement';

    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_TERMINATED = 'terminated';

    /** Holders that count as occupying (or about to occupy) the office. */
    public const OCCUPYING_STATUSES = [self::STATUS_SCHEDULED, self::STATUS_ACTIVE];

    protected $table = 'election_office_holders';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'school_branch_id',
        'election_id',
        'election_role_id',
        'election_candidate_id',
        'holderable_type',
        'holderable_id',
        'start_school_academic_year_id',
        'end_school_academic_year_id',
        'replaces_holder_id',
        'started_at',
        'expected_end_at',
        'ended_at',
        'appointment_type',
        'status',
        'vote_count',
        'remarks',
    ];

    protected $casts = [
        'started_at' => 'date',
        'expected_end_at' => 'date',
        'ended_at' => 'date',
        'vote_count' => 'integer',
    ];

    public function schoolBranch(): BelongsTo
    {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class, 'election_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(ElectionRole::class, 'election_role_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(ElectionCandidate::class, 'election_candidate_id');
    }

    public function holderable(): MorphTo
    {
        return $this->morphTo();
    }

    public function startSchoolAcademicYear(): BelongsTo
    {
        return $this->belongsTo(SchoolAcademicYear::class, 'start_school_academic_year_id');
    }

    public function endSchoolAcademicYear(): BelongsTo
    {
        return $this->belongsTo(SchoolAcademicYear::class, 'end_school_academic_year_id');
    }

    public function officeYears(): HasMany
    {
        return $this->hasMany(ElectionOfficeYear::class, 'election_office_holder_id')->orderBy('term_year_number');
    }

    /** The holder this record replaced (for replacement appointments). */
    public function replacedHolder(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replaces_holder_id');
    }

    public function replacement(): HasOne
    {
        return $this->hasOne(self::class, 'replaces_holder_id');
    }

    public function scopeOccupying(Builder $query): Builder
    {
        return $query->whereIn('status', self::OCCUPYING_STATUSES);
    }
}
