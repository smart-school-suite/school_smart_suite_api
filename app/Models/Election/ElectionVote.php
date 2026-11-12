<?php

namespace App\Models\Election;

use App\Models\Schoolbranches;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * An individual ballot. Results are always derived from this table.
 */
class ElectionVote extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'election_votes';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'school_branch_id',
        'election_id',
        'position_id',
        'candidate_id',
        'votable_type',
        'votable_id',
        'voted_at',
    ];

    protected $casts = [
        'voted_at' => 'datetime',
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
        return $this->belongsTo(ElectionRole::class, 'position_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(ElectionCandidate::class, 'candidate_id');
    }

    /** The voter (Student / Teacher / Schooladmin). */
    public function votable(): MorphTo
    {
        return $this->morphTo();
    }
}
