<?php

namespace App\Models\Election;

use App\Models\Schoolbranches;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ElectionCandidate extends Model
{
    use HasFactory, HasUuids;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_DISQUALIFIED = 'disqualified';
    public const STATUS_WITHDRAWN = 'withdrawn';

    protected $table = 'election_candidates';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'school_branch_id',
        'election_id',
        'election_role_id',
        'application_id',
        'candidateable_type',
        'candidateable_id',
        'status',
        'status_reason',
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

    public function application(): BelongsTo
    {
        return $this->belongsTo(ElectionApplication::class, 'application_id');
    }

    public function candidateable(): MorphTo
    {
        return $this->morphTo();
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ElectionVote::class, 'candidate_id');
    }

    public function officeHolder(): HasOne
    {
        return $this->hasOne(ElectionOfficeHolder::class, 'election_candidate_id');
    }
}
