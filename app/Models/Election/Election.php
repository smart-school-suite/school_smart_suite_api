<?php

namespace App\Models\Election;

use App\Models\AcademicYear\SystemAcademicYear;
use App\Models\Schoolbranches;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;

class Election extends Model
{
    use HasFactory, HasUuids;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_UPCOMING = 'upcoming';
    public const STATUS_APPLICATION_OPEN = 'application_open';
    public const STATUS_APPLICATION_CLOSED = 'application_closed';
    public const STATUS_VOTING_OPEN = 'voting_open';
    public const STATUS_VOTING_CLOSED = 'voting_closed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /** Statuses the scheduler may move automatically based on the configured windows. */
    public const SCHEDULED_STATUSES = [
        self::STATUS_UPCOMING,
        self::STATUS_APPLICATION_OPEN,
        self::STATUS_APPLICATION_CLOSED,
        self::STATUS_VOTING_OPEN,
    ];

    /** Statuses in which the configuration can no longer be edited freely. */
    public const LOCKED_STATUSES = [
        self::STATUS_VOTING_OPEN,
        self::STATUS_VOTING_CLOSED,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    protected $table = 'elections';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'title',
        'description',
        'status',
        'election_type_id',
        'academic_year_id',
        'school_branch_id',
        'application_start',
        'application_end',
        'voting_start',
        'voting_end',
        'participants',
        'max_applications_per_participant',
        'allow_candidate_self_vote',
        'is_results_published',
        'results_published_at',
    ];

    protected $casts = [
        'application_start' => 'datetime',
        'application_end' => 'datetime',
        'voting_start' => 'datetime',
        'voting_end' => 'datetime',
        'results_published_at' => 'datetime',
        'participants' => 'array',
        'is_results_published' => 'boolean',
        'allow_candidate_self_vote' => 'boolean',
        'max_applications_per_participant' => 'integer',
    ];

    // ------------------------------------------------------- relationships

    public function schoolBranch(): BelongsTo
    {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

    public function electionType(): BelongsTo
    {
        return $this->belongsTo(ElectionType::class, 'election_type_id');
    }

    /** The system academic year the election is held for. */
    public function systemAcademicYear(): BelongsTo
    {
        return $this->belongsTo(SystemAcademicYear::class, 'academic_year_id');
    }

    /** Positions are configured on the election type. */
    public function positions(): HasMany
    {
        return $this->hasMany(ElectionRole::class, 'election_type_id', 'election_type_id');
    }

    /** Resolved eligibility rows (the JSON `participants` column holds the configuration). */
    public function electionParticipants(): HasMany
    {
        return $this->hasMany(ElectionParticipant::class, 'election_id');
    }

    public function voters(): HasMany
    {
        return $this->electionParticipants()->where('can_vote', true);
    }

    public function eligibleApplicants(): HasMany
    {
        return $this->electionParticipants()->where('can_apply', true);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(ElectionApplication::class, 'election_id');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(ElectionCandidate::class, 'election_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ElectionVote::class, 'election_id');
    }

    public function officeHolders(): HasMany
    {
        return $this->hasMany(ElectionOfficeHolder::class, 'election_id');
    }

    public function officeYears(): HasManyThrough
    {
        return $this->hasManyThrough(
            ElectionOfficeYear::class,
            ElectionOfficeHolder::class,
            'election_id',
            'election_office_holder_id'
        );
    }

    // -------------------------------------------------------------- scopes

    public function scopeForBranch(Builder $query, string $schoolBranchId): Builder
    {
        return $query->where('school_branch_id', $schoolBranchId);
    }

    // ------------------------------------------------------------- helpers

    public function isApplicationWindowOpen(?Carbon $at = null): bool
    {
        $at ??= Carbon::now();
        return $this->status === self::STATUS_APPLICATION_OPEN
            && $this->application_start && $this->application_end
            && $at->between($this->application_start, $this->application_end);
    }

    public function isVotingWindowOpen(?Carbon $at = null): bool
    {
        $at ??= Carbon::now();
        return $this->status === self::STATUS_VOTING_OPEN
            && $this->voting_start && $this->voting_end
            && $at->between($this->voting_start, $this->voting_end);
    }

    public function isLocked(): bool
    {
        return in_array($this->status, self::LOCKED_STATUSES, true);
    }

    /** Status implied by the configured windows at a point in time. */
    public function statusForDate(?Carbon $at = null): string
    {
        $at ??= Carbon::now();

        if ($this->voting_end && $at->greaterThan($this->voting_end)) {
            return self::STATUS_VOTING_CLOSED;
        }
        if ($this->voting_start && $at->greaterThanOrEqualTo($this->voting_start)) {
            return self::STATUS_VOTING_OPEN;
        }
        if ($this->application_end && $at->greaterThan($this->application_end)) {
            return self::STATUS_APPLICATION_CLOSED;
        }
        if ($this->application_start && $at->greaterThanOrEqualTo($this->application_start)) {
            return self::STATUS_APPLICATION_OPEN;
        }
        return self::STATUS_UPCOMING;
    }
}
